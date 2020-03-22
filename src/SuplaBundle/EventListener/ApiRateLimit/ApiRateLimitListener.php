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

use Psr\Cache\CacheItemPoolInterface;
use SuplaBundle\Model\CurrentUserAware;
use SuplaBundle\Model\TimeProvider;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\KernelEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class ApiRateLimitListener {
    use CurrentUserAware;

    /** @var CacheItemPoolInterface */
    private $cache;
    /** @var bool */
    private $enabled;
    /** @var string */
    private $globalLimit;
    /** @var GlobalApiRateLimit */
    private $globalApiRateLimit;
    /** @var TimeProvider */
    private $timeProvider;

    /** @var ApiRateLimitStatus */
    private $rateLimit;

    public function __construct(
        bool $enabled,
        GlobalApiRateLimit $globalApiRateLimit,
        CacheItemPoolInterface $cache,
        TimeProvider $timeProvider
    ) {
        $this->cache = $cache;
        $this->globalApiRateLimit = $globalApiRateLimit;
        $this->enabled = $enabled;
        $this->timeProvider = $timeProvider;
    }

    public function onKernelRequest(GetResponseEvent $event) {
        if (!$this->isRequestRateLimited($event)) {
            return;
        }
        if ($this->incAndCheckGlobalRate()->isExceeded()) {
            $response = new Response('API cannot respond right now. Wait a while before subsequent request.', Response::HTTP_TOO_MANY_REQUESTS);
            $event->setResponse($response);
            $event->stopPropagation();
            return;
        }
    }

    public function onKernelResponse(FilterResponseEvent $event) {
        if (!$this->isRequestRateLimited($event)) {
            return;
        }
    }

    private function isRequestRateLimited(KernelEvent $event): bool {
        if (!$this->enabled) {
            return false;
        }
        if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
            return false;
        }
        if (substr($event->getRequest()->getRequestUri(), 0, 5) != "/api/") {
            return true;
        }
        if (substr($event->getRequest()->getRequestUri(), 0, 8) != "/direct/") {
            return true;
        }
        return false;
    }

    private function incAndCheckGlobalRate(): ApiRateLimitStatus {
        return $this->incAndCheck('global', function () {
            return $this->globalApiRateLimit;
        });
    }

    private function incAndCheck(string $key, callable $ruleProducer): ApiRateLimitStatus {
        $item = $this->cache->getItem($key);
        if ($item->isHit()) {
            $rateLimitStatus = new ApiRateLimitStatus($item->get());
        } else {
            $rateLimitStatus = ApiRateLimitStatus::fromRule($ruleProducer(), $this->timeProvider);
            $item->expiresAt(new \DateTime('@' . $rateLimitStatus->getReset()));
        }
        $rateLimitStatus->decrement();
        $item->set($rateLimitStatus->toString());
        $this->cache->save($item);
        return $rateLimitStatus;
    }

    private function setRateLimitHeaders(Response $response) {
        if ($this->rateLimit) {
            $response->headers->add([
                'X-RateLimit-Limit' => $this->rateLimit->getLimit(),
                'X-RateLimit-Remaining' => $this->rateLimit->getRemaining(),
                'X-RateLimit-Reset' => $this->rateLimit->getReset(),
            ]);
        }
    }
}
