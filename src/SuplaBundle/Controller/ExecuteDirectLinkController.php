<?php
/*
 Copyright (C) AC SOFTWARE SP. Z O.O.
 
 This program is free software; you can redistribute it and/or
 modify it under the terms of the GNU General Public License
 as published by the Free Software Foundation; either version 2
 of the License, or (at your option) any later version.
 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.
 You should have received a copy of the GNU General Public License
 along with this program; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */

namespace SuplaBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use SuplaBundle\Entity\DirectLink;
use SuplaBundle\Entity\IODeviceChannelGroup;
use SuplaBundle\Enums\ActionableSubjectType;
use SuplaBundle\Enums\AuditedEvent;
use SuplaBundle\Enums\ChannelFunctionAction;
use SuplaBundle\Exception\ApiException;
use SuplaBundle\Model\Audit\Audit;
use SuplaBundle\Model\ChannelActionExecutor\ChannelActionExecutor;
use SuplaBundle\Model\ChannelStateGetter\ChannelStateGetter;
use SuplaBundle\Model\Transactional;
use SuplaBundle\Supla\SuplaServerAware;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;

class ExecuteDirectLinkController extends Controller {
    use Transactional;
    use SuplaServerAware;

    /** @var ChannelActionExecutor */
    private $channelActionExecutor;
    /** @var EncoderFactoryInterface */
    private $encoderFactory;
    /** @var ChannelStateGetter */
    private $channelStateGetter;
    /** @var Audit */
    private $audit;

    public function __construct(
        ChannelActionExecutor $channelActionExecutor,
        EncoderFactoryInterface $encoderFactory,
        ChannelStateGetter $channelStateGetter,
        Audit $audit
    ) {
        $this->channelActionExecutor = $channelActionExecutor;
        $this->encoderFactory = $encoderFactory;
        $this->channelStateGetter = $channelStateGetter;
        $this->audit = $audit;
    }

    /**
     * @Route("/direct/{directLink}/{slug}", methods={"GET"})
     * @Template()
     */
    public function directLinkOptionsAction(DirectLink $directLink, string $slug) {
        $this->ensureLinkCanBeUsed($directLink, $slug);
        return ['directLink' => $directLink];
    }

    /**
     * @Route("/direct/{directLink}/{slug}/{action}", methods={"GET", "PATCH"})
     */
    public function executeDirectLinkAction(DirectLink $directLink, string $slug, string $action, Request $request) {
        if ($directLink->getDisableHttpGet() && $request->isMethod(Request::METHOD_GET)) {
            $errorMessage = 'The action was prevented from being performed using an HTTP GET method that is not permitted.'; // i18n
            $this->audit->newEntry(AuditedEvent::DIRECT_LINK_EXECUTION_FAILURE())
                ->setIntParam($directLink->getId())
                ->setTextParam($errorMessage)
                ->buildAndFlush();
            return new JsonResponse(
                ['success' => false, 'error' => $errorMessage . ' You need to use HTTP PATCH request to execute this link.'],
                Response::HTTP_METHOD_NOT_ALLOWED
            );
        }
        try {
            try {
                $action = ChannelFunctionAction::fromString($action);
            } catch (\InvalidArgumentException $e) {
                throw new ApiException("Action $action is not supported.", Response::HTTP_NOT_FOUND, $e);
            }
            if (!in_array($action, $directLink->getAllowedActions())) {
                throw new ApiException("The action $action is not allowed for this direct link.", Response::HTTP_FORBIDDEN);
            }
            $this->ensureLinkCanBeUsed($directLink, $slug);
            return $this->transactional(function (EntityManagerInterface $entityManager) use ($request, $action, $slug, $directLink) {
                $this->audit->newEntry(AuditedEvent::DIRECT_LINK_EXECUTION())
                    ->setIntParam($directLink->getId())
                    ->setTextParam($action)
                    ->buildAndFlush();
                $directLink->markExecution($request);
                $entityManager->persist($directLink);
                if ($action->getId() === ChannelFunctionAction::READ) {
                    $state = $this->channelStateGetter->getState($directLink->getSubject());
                    if ($state == []) {
                        $state = new \stdClass();
                    }
                    return new JsonResponse($state, Response::HTTP_OK, ['Content-Type' => 'application/json']);
                } else {
                    $params = $request->query->all();
                    try {
                        $this->channelActionExecutor->validateActionParams($directLink->getSubject(), $action, $params);
                    } catch (\InvalidArgumentException $e) {
                        return $this->render(
                            '@Supla/ExecuteDirectLink/directLinkParamsRequired.html.twig',
                            ['directLink' => $directLink, 'action' => $action],
                            new Response('', Response::HTTP_BAD_REQUEST)
                        );
                    }
                    try {
                        $this->channelActionExecutor->executeAction($directLink->getSubject(), $action, $params);
                        return new JsonResponse(['success' => true], Response::HTTP_ACCEPTED);
                    } catch (ServiceUnavailableHttpException $e) {
                        $errorData = ['success' => false, 'supla_server_alive' => $this->suplaServer->isAlive()];
                        if ($directLink->getSubjectType() == ActionableSubjectType::CHANNEL()) {
                            $errorData['device_connected'] =
                                $this->suplaServer->isDeviceConnected($directLink->getSubject()->getIoDevice());
                        } elseif ($directLink->getSubjectType() == ActionableSubjectType::CHANNEL_GROUP()) {
                            $errorData['devices_connected'] = [];
                            /** @var IODeviceChannelGroup $channelGroup */
                            $channelGroup = $directLink->getSubject();
                            foreach ($channelGroup->getChannels() as $channel) {
                                $errorData['devices_connected'][$channel->getId()] =
                                    $this->suplaServer->isDeviceConnected($channel->getIoDevice());
                            }
                        }
                        return new JsonResponse($errorData, Response::HTTP_SERVICE_UNAVAILABLE);
                    }
                }
            });
        } catch (ApiException $e) {
            $this->audit->newEntry(AuditedEvent::DIRECT_LINK_EXECUTION_FAILURE())
                ->setIntParam($directLink->getId())
                ->setTextParam($e->getMessage())
                ->buildAndFlush();
            throw $e;
        }
    }

    private function ensureLinkCanBeUsed(DirectLink $directLink, string $slug) {
        if (!$directLink->isValidSlug($slug, $this->encoderFactory->getEncoder($directLink))) {
            throw new ApiException("Given verification code is invalid.", Response::HTTP_FORBIDDEN);
        }
        $directLink->ensureIsActive();
    }
}
