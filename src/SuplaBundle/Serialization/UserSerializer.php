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

namespace SuplaBundle\Serialization;

use SuplaBundle\Entity\Main\User;
use SuplaBundle\EventListener\ApiRateLimit\ApiRateLimitStatus;
use SuplaBundle\EventListener\ApiRateLimit\ApiRateLimitStorage;
use SuplaBundle\EventListener\ApiRateLimit\DefaultUserApiRateLimit;
use SuplaBundle\Model\ApiVersions;
use SuplaBundle\Model\TimeProvider;
use SuplaBundle\Repository\UserRepository;
use SuplaBundle\Supla\SuplaServerAware;
use SuplaBundle\Utils\DateUtils;

class UserSerializer extends AbstractSerializer {
    use SuplaServerAware;

    /** @var ApiRateLimitStorage */
    private $apiRateLimitStorage;
    /** @var DefaultUserApiRateLimit */
    private $defaultUserApiRateLimit;
    /** @var TimeProvider */
    private $timeProvider;
    /** @var UserRepository */
    private $userRepository;
    /** @var bool */
    private $apiRateLimitEnabled;

    public function __construct(
        ApiRateLimitStorage $apiRateLimitStorage,
        DefaultUserApiRateLimit $defaultUserApiRateLimit,
        TimeProvider $timeProvider,
        UserRepository $userRepository,
        bool $apiRateLimitEnabled
    ) {
        parent::__construct();
        $this->apiRateLimitStorage = $apiRateLimitStorage;
        $this->defaultUserApiRateLimit = $defaultUserApiRateLimit;
        $this->timeProvider = $timeProvider;
        $this->userRepository = $userRepository;
        $this->apiRateLimitEnabled = $apiRateLimitEnabled;
    }

    /**
     * @param \SuplaBundle\Entity\Main\User $user
     * @inheritdoc
     */
    protected function addExtraFields(array &$normalized, $user, array $context) {
        if ($this->isSerializationGroupRequested('limits', $context) && $this->apiRateLimitEnabled) {
            $rule = $user->getApiRateLimit() ?: $this->defaultUserApiRateLimit;
            $cacheItem = $this->apiRateLimitStorage->getItem($this->apiRateLimitStorage->getUserKey($user));
            $status = null;
            if ($cacheItem->isHit()) {
                $status = new ApiRateLimitStatus($cacheItem->get());
            }
            if (!$status || $status->isExpired($this->timeProvider)) {
                $status = ApiRateLimitStatus::fromRule($rule, $this->timeProvider);
            }
            $normalized['apiRateLimit'] = [
                'rule' => $rule->toArray(),
                'status' => $status->toArray(),
            ];
            $normalized['limits']['pushNotificationsPerHour'] = $this->suplaServer->getPushNotificationLimit($user);
        }
        if (ApiVersions::V2_4()->isRequestedEqualOrGreaterThan($context)) {
            if (!isset($normalized['relationsCount']) && $this->isSerializationGroupRequested('user.relationsCount', $context)) {
                $normalized['relationsCount'] = $this->userRepository->find($user->getId())->getRelationsCount();
            }
        }
        if ($this->isSerializationGroupRequested('sun', $context)) {
            $time = $this->timeProvider->getTimestamp();
            $lat = $user->getHomeLatitude();
            $lng = $user->getHomeLongitude();
            $sunInfo = DateUtils::wrapInTimezone($user->getTimezone(), fn() => date_sun_info($time, $lat, $lng));
            $normalized['closestSunset'] = $sunInfo['sunset'] ?: null;
            $normalized['closestSunrise'] = $sunInfo['sunrise'] ?: null;
        }
    }

    public function supportsNormalization($entity, $format = null) {
        return $entity instanceof User;
    }
}
