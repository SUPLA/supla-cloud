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
use SuplaBundle\Model\Audit\AuditAware;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

class ApiRateLimitListener {
    use AuditAware;

    /** @var CacheItemPoolInterface */
    private $cache;
    /** @var bool */
    private $enabled;
    /** @var string */
    private $globalLimit;

    public function __construct(bool $enabled, string $globalLimit, CacheItemPoolInterface $cache) {
        $this->cache = $cache;
        $this->globalLimit = $globalLimit;
        $this->enabled = $enabled;
    }

    public function onKernelRequest(GetResponseEvent $event) {
        $this->noop();
    }

    public function onKernelResponse(FilterResponseEvent $event) {
        $this->noop();
    }

    private function noop() {
    }
}
