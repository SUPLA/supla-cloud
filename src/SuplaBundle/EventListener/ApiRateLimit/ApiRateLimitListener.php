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

namespace SuplaBundle\EventListener\ApiRateLimit;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use SuplaBundle\Auth\Token\AccessIdAwareToken;
use SuplaBundle\Auth\Token\PublicOauthAppToken;
use SuplaBundle\Auth\Token\WebappToken;
use SuplaBundle\Entity\DirectLink;
use SuplaBundle\Entity\User;
use SuplaBundle\Model\CurrentUserAware;
use SuplaBundle\Model\TimeProvider;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\KernelEvent;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class ApiRateLimitListener {
    use CurrentUserAware;

    /** @var ApiRateLimitStorage */
    private $storage;
    /** @var bool */
    private $enabled;
    /** @var bool */
    private $blocking;
    /** @var GlobalApiRateLimit */
    private $globalApiRateLimit;
    /** @var DefaultUserApiRateLimit */
    private $defaultUserApiRateLimit;
    /** @var TimeProvider */
    private $timeProvider;
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var ApiRateLimitStatus */
    private $currentUserRateLimit;
    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        bool $enabled,
        bool $blocking,
        GlobalApiRateLimit $globalApiRateLimit,
        DefaultUserApiRateLimit $defaultUserApiRateLimit,
        ApiRateLimitStorage $storage,
        TimeProvider $timeProvider,
        EntityManagerInterface $entityManager,
        LoggerInterface $logger
    ) {
        $this->storage = $storage;
        $this->blocking = $blocking;
        $this->globalApiRateLimit = $globalApiRateLimit;
        $this->defaultUserApiRateLimit = $defaultUserApiRateLimit;
        $this->enabled = $enabled;
        $this->timeProvider = $timeProvider;
        $this->entityManager = $entityManager;
        $this->logger = $logger;
    }

    public function onKernelRequest(GetResponseEvent $event) {
        if (!$this->isRequestRateLimited($event)) {
            return;
        }
        if ($this->incAndCheckGlobalRate()->isExceeded()) {
            $this->preventRequestDueToLimitExceeded($event, 'API cannot respond right now. Wait a while before subsequent request.');
            return;
        }
        $userOrId = $this->getCurrentUserOrId($event);
        if ($userOrId) {
            $this->currentUserRateLimit = $this->incAndCheckUserRate($userOrId);
            if ($this->currentUserRateLimit->isExceeded()) {
                $this->preventRequestDueToLimitExceeded($event, 'You have reached your API rate limit. Slow down.');
            }
        }
    }

    private function preventRequestDueToLimitExceeded(GetResponseEvent $event, string $message) {
        if (!$this->blocking) {
            return;
        }
        $request = $event->getRequest();
        $isApiRequest = preg_match('#/api/#', $request->getRequestUri());
        if ($isApiRequest || in_array('application/json', $request->getAcceptableContentTypes())) {
            $data = ['status' => Response::HTTP_TOO_MANY_REQUESTS, 'message' => $message];
            $response = new JsonResponse($data, Response::HTTP_TOO_MANY_REQUESTS);
            $this->setRateLimitHeaders($response);
            $event->setResponse($response);
            $event->stopPropagation();
        } else {
            throw new TooManyRequestsHttpException();
        }
    }

    public function onKernelResponse(FilterResponseEvent $event) {
        if (!$this->isRequestRateLimited($event)) {
            return;
        }
        $this->setRateLimitHeaders($event->getResponse());
    }

    private function isRequestRateLimited(KernelEvent $event): bool {
        if (!$this->enabled) {
            return false;
        }
        if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
            return false;
        }
        $userToken = $this->getCurrentUserToken();
        if ($userToken instanceof WebappToken || $userToken instanceof AccessIdAwareToken || $userToken instanceof PublicOauthAppToken) {
            return false;
        }
        $uri = $event->getRequest()->getRequestUri();
        if (substr($uri, 0, 5) === '/api/') {
            return $uri !== '/api/server-status';
        }
        if (substr($uri, 0, 8) === '/direct/') {
            return true;
        }
        return false;
    }

    private function incAndCheckGlobalRate(): ApiRateLimitStatus {
        return $this->incAndCheck($this->storage->getGlobalKey(), function () {
            return $this->globalApiRateLimit;
        });
    }

    private function incAndCheckUserRate($userOrId): ApiRateLimitStatus {
        return $this->incAndCheck($this->storage->getUserKey($userOrId), function () use ($userOrId) {
            $user = $userOrId instanceof User ? $userOrId : $this->entityManager->find(User::class, $userOrId);
            return $user->getApiRateLimit() ?: $this->defaultUserApiRateLimit;
        });
    }

    private function incAndCheck(string $key, callable $ruleProducer): ApiRateLimitStatus {
        $item = $this->storage->getItem($key);
        $rateLimitStatus = null;
        if ($item->isHit()) {
            $rateLimitStatus = new ApiRateLimitStatus($item->get());
            if ($rateLimitStatus->isExpired($this->timeProvider)) {
                if ($rateLimitStatus->isExceeded()) {
                    $data = array_merge($rateLimitStatus->toArray(), ['excess' => $rateLimitStatus->getExcess(), 'key' => $key]);
                    $this->logger->warning('Renewing exceeded API rate limit: ' . $key, $data);
                }
                $rateLimitStatus = null;
            }
        }
        if (!$rateLimitStatus) {
            $rateLimitStatus = ApiRateLimitStatus::fromRule($ruleProducer(), $this->timeProvider);
        }
        $rateLimitStatus->decrement();
        $item->set($rateLimitStatus->toString());
        $item->expiresAfter(1209600); // if not checked in 14 days, expire
        $this->storage->save($item);
        return $rateLimitStatus;
    }

    private function getCurrentUserOrId(KernelEvent $event) {
        if ($user = $this->getCurrentUser()) {
            return $user;
        } elseif (substr($event->getRequest()->getRequestUri(), 0, 8) === "/direct/") {
            preg_match('#^/direct/(\d+)/.+$#', $event->getRequest()->getRequestUri(), $match);
            if ($match) {
                $directLinkId = intval($match[1]);
                $directLinkOwnerId = $this->storage->getItem($this->storage->getDirectLinkOwnerIdKey($directLinkId));
                if ($directLinkOwnerId->isHit()) {
                    return $directLinkOwnerId->get();
                } else {
                    /** @var DirectLink $directLink */
                    $directLink = $this->entityManager->find(DirectLink::class, $directLinkId);
                    $directLinkOwner = $directLink->getUser();
                    $directLinkOwnerId->set($directLinkOwner->getId());
                    $directLinkOwnerId->expiresAfter(31536000); // one year
                    $this->storage->save($directLinkOwnerId);
                    return $directLinkOwner;
                }
            }
        }
    }

    private function setRateLimitHeaders(Response $response) {
        if ($this->currentUserRateLimit) {
            $response->headers->add([
                'X-RateLimit-Limit' => $this->currentUserRateLimit->getLimit(),
                'X-RateLimit-Remaining' => $this->currentUserRateLimit->getRemaining(),
                'X-RateLimit-Reset' => $this->currentUserRateLimit->getReset(),
            ]);
        }
    }
}
