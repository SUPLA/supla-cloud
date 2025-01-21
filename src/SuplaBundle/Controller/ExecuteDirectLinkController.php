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

use Assert\Assertion;
use Exception;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use SuplaBundle\Entity\HasIcon;
use SuplaBundle\Entity\Main\DirectLink;
use SuplaBundle\Entity\Main\IODeviceChannelGroup;
use SuplaBundle\Enums\ActionableSubjectType;
use SuplaBundle\Enums\AuditedEvent;
use SuplaBundle\Enums\ChannelFunctionAction;
use SuplaBundle\Enums\DirectLinkExecutionFailureReason;
use SuplaBundle\Exception\DirectLinkExecutionFailureException;
use SuplaBundle\Exception\SceneDuringExecutionException;
use SuplaBundle\Exception\SceneDuringInactivePeriodException;
use SuplaBundle\Model\ApiVersions;
use SuplaBundle\Model\Audit\Audit;
use SuplaBundle\Model\ChannelActionExecutor\ChannelActionExecutor;
use SuplaBundle\Model\ChannelStateGetter\ChannelStateGetter;
use SuplaBundle\Model\Transactional;
use SuplaBundle\Supla\SuplaServerAware;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Translation\TranslatorInterface;

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
    /** @var TranslatorInterface */
    private $translator;
    /** @var LoggerInterface */
    private $logger;
    /** @var NormalizerInterface */
    private $normalizer;

    public function __construct(
        ChannelActionExecutor $channelActionExecutor,
        EncoderFactoryInterface $encoderFactory,
        ChannelStateGetter $channelStateGetter,
        Audit $audit,
        TranslatorInterface $translator,
        LoggerInterface $logger,
        NormalizerInterface $normalizer
    ) {
        $this->channelActionExecutor = $channelActionExecutor;
        $this->encoderFactory = $encoderFactory;
        $this->channelStateGetter = $channelStateGetter;
        $this->audit = $audit;
        $this->translator = $translator;
        $this->logger = $logger;
        $this->normalizer = $normalizer;
    }

    /**
     * @Route("/direct/{directLinkId}/{slug}", methods={"GET"})
     */
    public function directLinkOptionsAction(Request $request) {
        return $this->returnDirectLinkErrorIfException($request, function () use ($request) {
            $directLink = $this->getDirectLink($request);
            $this->ensureLinkCanBeUsed($request, $directLink, $request->get('slug', ''));
            return $this->directLinkResponse($directLink, null, 'html', Response::HTTP_OK);
        });
    }

    /**
     * @Route("/direct/{directLinkId}", methods={"PATCH"})
     * @Route("/direct/{directLinkId}/{slug}/{action}", methods={"GET", "PATCH"})
     */
    public function executeDirectLinkAction(Request $request) {
        return $this->returnDirectLinkErrorIfException($request, function () use ($request) {
            [$slug, $action] = self::getSlugAndAction($request);
            $directLink = $this->getDirectLink($request);
            $responseType = $this->determineResponseType($request);
            $this->ensureLinkCanBeExecuted($directLink, $request, $slug, $action);
            $executionResult = $this->executeDirectLink($directLink, $request, $action);
            $this->audit->newEntry(AuditedEvent::DIRECT_LINK_EXECUTION())
                ->setIntParam($directLink->getId())
                ->setTextParam($action->getValue())
                ->buildAndSave();
            $directLink->markExecution($request);
            $this->entityManager->persist($directLink);
            $responseStatus = $action == ChannelFunctionAction::READ() ? Response::HTTP_OK : Response::HTTP_ACCEPTED;
            return $this->directLinkResponse($directLink, $action, $responseType, $responseStatus, $executionResult);
        });
    }

    private function getDirectLink(Request $request): DirectLink {
        $directLink = $this->entityManager->find(DirectLink::class, $request->get('directLinkId'));
        if (!$directLink) {
            throw new DirectLinkExecutionFailureException(DirectLinkExecutionFailureReason::INVALID_SLUG(), [], Response::HTTP_FORBIDDEN);
        }
        return $directLink;
    }

    private function returnDirectLinkErrorIfException(Request $request, callable $callback) {
        $responseType = $this->determineResponseType($request);
        try {
            $directLink = $this->getDirectLink($request);
        } catch (Exception $e) {
            $directLink = null;
        }
        try {
            $result = $callback();
            $this->entityManager->flush();
            return $result;
        } catch (DirectLinkExecutionFailureException $executionException) {
        } catch (SceneDuringExecutionException $e) {
            $executionException = new DirectLinkExecutionFailureException(
                DirectLinkExecutionFailureReason::SCENE_DURING_EXECUTION(),
                [],
                Response::HTTP_CONFLICT,
                $e
            );
        } catch (SceneDuringInactivePeriodException $e) {
            $executionException = new DirectLinkExecutionFailureException(
                DirectLinkExecutionFailureReason::SCENE_INACTIVE(),
                [],
                Response::HTTP_CONFLICT,
                $e
            );
        } catch (ConflictHttpException $e) {
            $executionException = new DirectLinkExecutionFailureException(
                DirectLinkExecutionFailureReason::INVALID_CHANNEL_STATE(),
                [],
                Response::HTTP_CONFLICT,
                $e
            );
        } catch (Exception $otherException) {
            $errorData = ['success' => false, 'supla_server_status' => $this->suplaServer->getServerStatus()];
            if ($directLink) {
                if ($directLink->getSubjectType() == ActionableSubjectType::CHANNEL()) {
                    $errorData['device_connected'] =
                        $this->suplaServer->isChannelConnected($directLink->getSubject());
                } elseif ($directLink->getSubjectType() == ActionableSubjectType::CHANNEL_GROUP()) {
                    $errorData['devices_connected'] = [];
                    /** @var \SuplaBundle\Entity\Main\IODeviceChannelGroup $channelGroup */
                    $channelGroup = $directLink->getSubject();
                    foreach ($channelGroup->getChannels() as $channel) {
                        $errorData['devices_connected'][$channel->getId()] =
                            $this->suplaServer->isChannelConnected($channel);
                    }
                }
            }
            if (!($otherException instanceof ServiceUnavailableHttpException) || !$errorData['supla_server_alive']) {
                $this->logger->error(
                    $otherException->getMessage(),
                    array_merge(['stackTrace' => $otherException->getTraceAsString()], $errorData)
                );
            }
            $executionException = new DirectLinkExecutionFailureException(
                DirectLinkExecutionFailureReason::OTHER_FAILURE(),
                $errorData,
                Response::HTTP_SERVICE_UNAVAILABLE,
                $otherException
            );
        }
        $reason = $executionException->getReason();
        try {
            $action = self::getSlugAndAction($request)[1];
        } catch (Exception $e) {
            $action = $request->get('action', null);
        }
        $response = $this->directLinkResponse(
            $directLink,
            $action,
            $responseType,
            $executionException->getStatusCode(),
            $executionException->getDetails(),
            $reason
        );
        if ($directLink) {
            $this->audit->newEntry(AuditedEvent::DIRECT_LINK_EXECUTION_FAILURE())
                ->setIntParam($directLink->getId())
                ->setTextParam(json_encode(['reason' => $reason->getValue(), 'details' => $executionException->getDetails()]))
                ->buildAndSave();
        }
        $this->entityManager->flush();
        return $response;
    }

    private function ensureLinkCanBeExecuted(DirectLink $directLink, Request $request, string $slug, ChannelFunctionAction $action) {
        $this->ensureLinkCanBeUsed($request, $directLink, $slug);
        if (!in_array($action, $directLink->getAllowedActions())) {
            throw new DirectLinkExecutionFailureException(
                DirectLinkExecutionFailureReason::FORBIDDEN_ACTION(),
                ['action_id' => $action->getValue(), 'action_name' => $action->getNameSlug()]
            );
        }
    }

    private function ensureLinkCanBeUsed(Request $request, DirectLink $directLink, string $slug) {
        if ($directLink->getDisableHttpGet() && $request->isMethod(Request::METHOD_GET)) {
            throw new DirectLinkExecutionFailureException(
                DirectLinkExecutionFailureReason::HTTP_GET_FORBIDDEN(),
                [],
                Response::HTTP_METHOD_NOT_ALLOWED
            );
        }
        if (!$directLink->isValidSlug($slug, $this->encoderFactory->getEncoder($directLink))) {
            throw new DirectLinkExecutionFailureException(DirectLinkExecutionFailureReason::INVALID_SLUG(), [], Response::HTTP_FORBIDDEN);
        }
        $directLink->ensureIsActive();
    }

    public static function getSlugAndAction(Request $request): array {
        $slug = $request->get('slug');
        $action = $request->get('action', 'read');
        if (!$slug && $request->isMethod(Request::METHOD_PATCH)) {
            $requestPayload = $request->request->all();
            if (!is_array($requestPayload) || !isset($requestPayload['code']) || !isset($requestPayload['action'])) {
                throw new DirectLinkExecutionFailureException(DirectLinkExecutionFailureReason::NO_SLUG_OR_ACTION());
            }
            $slug = $requestPayload['code'];
            $action = $requestPayload['action'];
        }
        if (!$slug || !is_string($slug) || !is_string($action) || !$action) {
            throw new DirectLinkExecutionFailureException(DirectLinkExecutionFailureReason::NO_SLUG_OR_ACTION());
        }
        try {
            $action = ChannelFunctionAction::fromString($action);
        } catch (InvalidArgumentException $e) {
            throw new DirectLinkExecutionFailureException(DirectLinkExecutionFailureReason::UNSUPPORTED_ACTION(), ['action' => $action]);
        }
        return [$slug, $action];
    }

    private function executeDirectLink(DirectLink $directLink, Request $request, ChannelFunctionAction $action): array {
        if ($action->getId() === ChannelFunctionAction::READ) {
            return $this->channelStateGetter->getState($directLink->getSubject());
        } else {
            $params = $request->query->all();
            if (isset($params['format'])) {
                unset($params['format']);
            }
            try {
                $this->channelActionExecutor->validateAndTransformActionParamsFromApi($directLink->getSubject(), $action, $params);
            } catch (InvalidArgumentException $e) {
                throw new DirectLinkExecutionFailureException(DirectLinkExecutionFailureReason::INVALID_ACTION_PARAMETERS());
            }
            $this->channelActionExecutor->executeAction($directLink->getSubject(), $action, $params);
            return [];
        }
    }

    private function determineResponseType(Request $request): string {
        if ($format = $request->get('format')) {
            Assertion::inArray($format, ['json', 'html', 'plain'], 'Invalid response format requested.');
            return $format;
        }
        if ($request->isMethod(Request::METHOD_PATCH) || in_array('application/json', $request->getAcceptableContentTypes())) {
            return 'json';
        } elseif (in_array('text/html', $request->getAcceptableContentTypes())) {
            return 'html';
        } elseif (in_array('text/plain', $request->getAcceptableContentTypes())) {
            return 'plain';
        } else {
            return 'json';
        }
    }

    /**
     * @param \SuplaBundle\Entity\Main\DirectLink|null $directLink
     */
    private function directLinkResponse(
        $directLink,
        $action,
        string $responseType,
        int $responseCode,
        array $data = [],
        DirectLinkExecutionFailureReason $failureReason = null
    ): Response {
        if ($responseType == 'html') {
            $exposeLinkReasons = [
                DirectLinkExecutionFailureReason::INVALID_ACTION_PARAMETERS,
                DirectLinkExecutionFailureReason::INVALID_CHANNEL_STATE,
            ];
            if ($failureReason && !in_array($failureReason->getValue(), $exposeLinkReasons)) {
                $normalized = [];
            } else {
                $normalizationContext = ['groups' => ['basic', 'images'], 'version' => ApiVersions::V2_4];
                $subject = $directLink->getSubject();
                if (!$data) {
                    try {
                        $data = $this->channelStateGetter->getState($subject);
                    } catch (\Exception $e) {
                    }
                }
                $normalized = [
                    'id' => $directLink->getId(),
                    'caption' => $directLink->getCaption(),
                    'allowedActions' => $this->normalizer->normalize($directLink->getAllowedActions(), null, $normalizationContext),
                    'subject' => $this->normalizer->normalize($subject, null, $normalizationContext),
                    'state' => $data,
                ];
                if ($subject instanceof HasIcon) {
                    $normalized['subject']['userIcon'] = $this->normalizer->normalize($subject->getUserIcon(), null, $normalizationContext);
                    if ($subject instanceof IODeviceChannelGroup) {
                        $normalized['channels'] = [];
                        foreach ($subject->getChannels() as $channel) {
                            $channelData = $this->normalizer->normalize($channel, null, $normalizationContext);
                            $channelData['userIcon'] = $this->normalizer->normalize($channel->getUserIcon(), null, $normalizationContext);
                            $normalized['channels'][] = $channelData;
                        }
                    }
                }
            }
            return $this->render(
                '@Supla/ExecuteDirectLink/directLinkHtmlResponse.html.twig',
                ['directLink' => $normalized, 'action' => $action, 'failureReason' => $failureReason],
                new Response('', $responseCode)
            );
        } elseif ($responseType == 'plain') {
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
            return new Response($message, $responseCode, ['Content-Type' => 'text/' . $responseType]);
        } else {
            $response = $data ?: ['success' => !$failureReason];
            if ($failureReason) {
                $response['message'] = $failureReason->getValue();
                $response['messageText'] = $this->translator->trans($failureReason->getValue());
            }
            return new JsonResponse($response, $responseCode);
        }
    }
}
