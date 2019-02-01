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
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use SuplaBundle\Entity\DirectLink;
use SuplaBundle\Entity\IODeviceChannelGroup;
use SuplaBundle\Enums\ActionableSubjectType;
use SuplaBundle\Enums\AuditedEvent;
use SuplaBundle\Enums\ChannelFunctionAction;
use SuplaBundle\Enums\DirectLinkExecutionFailureReason;
use SuplaBundle\Exception\DirectLinkExecutionFailureException;
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
use Symfony\Component\Translation\Translator;

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
    /** @var EntityManagerInterface */
    private $entityManager;
    /** @var Translator */
    private $translator;
    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        EntityManagerInterface $entityManager,
        ChannelActionExecutor $channelActionExecutor,
        EncoderFactoryInterface $encoderFactory,
        ChannelStateGetter $channelStateGetter,
        Audit $audit,
        Translator $translator,
        LoggerInterface $logger
    ) {
        $this->entityManager = $entityManager;
        $this->channelActionExecutor = $channelActionExecutor;
        $this->encoderFactory = $encoderFactory;
        $this->channelStateGetter = $channelStateGetter;
        $this->audit = $audit;
        $this->translator = $translator;
        $this->logger = $logger;
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
     * @Route("/direct/{directLink}", methods={"PATCH"})
     * @Route("/direct/{directLink}/{slug}/{action}", methods={"GET", "PATCH"})
     */
    public function executeDirectLinkAction(DirectLink $directLink, Request $request, string $slug = null, string $action = null) {
        $responseType = $this->determineResponseType($request);
        try {
            $action = $this->ensureLinkCanBeExecuted($directLink, $request, $slug, $action);
            $executionResult = $this->executeDirectLink($directLink, $request, $action);
            $this->audit->newEntry(AuditedEvent::DIRECT_LINK_EXECUTION())
                ->setIntParam($directLink->getId())
                ->setTextParam($action->getValue())
                ->buildAndSave();
            $responseStatus = $action == ChannelFunctionAction::READ() ? Response::HTTP_OK : Response::HTTP_ACCEPTED;
            $response = $this->directLinkResponse($directLink, $action, $responseType, $responseStatus, $executionResult);
        } catch (DirectLinkExecutionFailureException $executionException) {
        } catch (\Exception $otherException) {
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
            if (!($otherException instanceof ServiceUnavailableHttpException) || !$errorData['supla_server_alive']) {
                $this->logger->error($otherException->getMessage(), array_merge(['exception' => $otherException], $errorData));
            }
            $executionException = new DirectLinkExecutionFailureException(
                DirectLinkExecutionFailureReason::OTHER_FAILURE(),
                $errorData,
                Response::HTTP_SERVICE_UNAVAILABLE,
                $otherException
            );
        }
        $directLink->markExecution($request);
        $this->entityManager->persist($directLink);
        if (isset($executionException)) {
            $reason = $executionException->getReason();
            $response = $this->directLinkResponse(
                $directLink,
                $action,
                $responseType,
                $executionException->getStatusCode(),
                $executionException->getDetails(),
                $reason
            );
            $this->audit->newEntry(AuditedEvent::DIRECT_LINK_EXECUTION_FAILURE())
                ->setIntParam($directLink->getId())
                ->setTextParam(json_encode(['reason' => $reason->getValue(), 'details' => $executionException->getDetails()]))
                ->buildAndSave();
        }
        $this->entityManager->flush();
        return $response;
    }

    private function ensureLinkCanBeExecuted(
        DirectLink $directLink,
        Request $request,
        string $slug = null,
        string $action = null
    ): ChannelFunctionAction {
        if ($directLink->getDisableHttpGet() && $request->isMethod(Request::METHOD_GET)) {
            throw new DirectLinkExecutionFailureException(
                DirectLinkExecutionFailureReason::HTTP_GET_FORBIDDEN(),
                [],
                Response::HTTP_METHOD_NOT_ALLOWED
            );
        }
        if (!$slug) {
            $requestPayload = $request->request->all();
            if (!is_array($requestPayload) || !isset($requestPayload['code']) || !isset($requestPayload['action'])) {
                throw new DirectLinkExecutionFailureException(DirectLinkExecutionFailureReason::NO_SLUG_OR_ACTION());
            }
            $slug = $requestPayload['code'];
            $action = $requestPayload['action'];
            if (!$slug || !is_string($slug) || !is_string($action) || !$action) {
                throw new DirectLinkExecutionFailureException(DirectLinkExecutionFailureReason::NO_SLUG_OR_ACTION());
            }
        }
        try {
            $action = ChannelFunctionAction::fromString($action);
        } catch (\InvalidArgumentException $e) {
            throw new DirectLinkExecutionFailureException(DirectLinkExecutionFailureReason::UNSUPPORTED_ACTION(), ['action' => $action]);
        }
        if (!in_array($action, $directLink->getAllowedActions())) {
            throw new DirectLinkExecutionFailureException(
                DirectLinkExecutionFailureReason::FORBIDDEN_ACTION(),
                ['action_id' => $action->getValue(), 'action_name' => $action->getNameSlug()]
            );
        }
        $this->ensureLinkCanBeUsed($directLink, $slug);
        return $action;
    }

    private function executeDirectLink(DirectLink $directLink, Request $request, ChannelFunctionAction $action): array {
        if ($action->getId() === ChannelFunctionAction::READ) {
            return $this->channelStateGetter->getState($directLink->getSubject());
        } else {
            $params = $request->query->all();
            try {
                $this->channelActionExecutor->validateActionParams($directLink->getSubject(), $action, $params);
            } catch (\InvalidArgumentException $e) {
                throw new DirectLinkExecutionFailureException(DirectLinkExecutionFailureReason::INVALID_ACTION_PARAMETERS());
            }
            $this->channelActionExecutor->executeAction($directLink->getSubject(), $action, $params);
            return [];
        }
    }

    private function ensureLinkCanBeUsed(DirectLink $directLink, string $slug) {
        if (!$directLink->isValidSlug($slug, $this->encoderFactory->getEncoder($directLink))) {
            throw new DirectLinkExecutionFailureException(DirectLinkExecutionFailureReason::INVALID_SLUG(), [], Response::HTTP_FORBIDDEN);
        }
        $directLink->ensureIsActive();
    }

    private function determineResponseType(Request $request): string {
        if (in_array('text/html', $request->getAcceptableContentTypes())) {
            return 'html';
        } else if (in_array('text/plain', $request->getAcceptableContentTypes())) {
            return 'plain';
        } else {
            return 'json';
        }
    }

    private function directLinkResponse(
        DirectLink $directLink,
        $action,
        string $responseType,
        int $responseCode,
        array $data = [],
        DirectLinkExecutionFailureReason $failureReason = null
    ): Response {
        if ($responseType == 'html') {
            return $this->render(
                '@Supla/ExecuteDirectLink/directLinkHtmlResponse.html.twig',
                ['directLink' => $directLink, 'action' => $action, 'failureReason' => $failureReason, 'details' => $data],
                new Response('', $responseCode)
            );
        }
        if ($responseType == 'html' || $responseType == 'plain') {
            $message = '';
            if ($data) {
                $data = array_map(function ($value, $key) {
                    return strtoupper($key) . ': ' . (is_string($value) ? $value : json_encode($value));
                }, $data, array_keys($data));
                $data = implode(PHP_EOL, $data);
                $message = $data;
            }
            if ($failureReason) {
                $message = trim($this->translator->trans($failureReason->getValue()) . PHP_EOL . $message);
            }
            if (!$message) {
                $message = 'OK';
            }
            if ($responseType == 'html') {
                $htmlColor = $failureReason ? 'red' : 'green';
                $message = '<div style="color: ' . $htmlColor . '">' . nl2br($message) . '</div>';
            }
            return new Response($message, $responseCode, ['Content-Type' => 'text/' . $responseType]);
        } else {
            $response = $data ?: ['success' => !$failureReason];
            if ($failureReason) {
                $response['reason'] = $failureReason->getValue();
                $response['message'] = $this->translator->trans($failureReason->getValue());
            }
            return new JsonResponse($response, $responseCode);
        }
    }
}
