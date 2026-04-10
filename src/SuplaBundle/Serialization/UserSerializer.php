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
use SuplaBundle\EventListener\ApiRateLimit\ApiRateLimitRules;
use SuplaBundle\EventListener\ApiRateLimit\ApiRateLimitStatus;
use SuplaBundle\EventListener\ApiRateLimit\ApiRateLimitStorage;
use SuplaBundle\Model\ApiVersions;
use SuplaBundle\Model\TimeProvider;
use SuplaBundle\Repository\UserRepository;
use SuplaBundle\Supla\SuplaServerAware;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

class UserSerializer extends AbstractSerializer implements NormalizerAwareInterface {
    use SuplaServerAware;
    use NormalizerAwareTrait;

    public function __construct(
        private readonly ApiRateLimitStorage $apiRateLimitStorage,
        private readonly ApiRateLimitRules $rateLimitRules,
        private readonly TimeProvider $timeProvider,
        private readonly UserRepository $userRepository,
        private readonly bool $apiRateLimitEnabled
    ) {
        parent::__construct();
    }

    /**
     * @param \SuplaBundle\Entity\Main\User $user
     * @inheritdoc
     */
    protected function addExtraFields(array &$normalized, $user, array $context) {
        if ($this->isSerializationGroupRequested('limits', $context) && $this->apiRateLimitEnabled) {
            $rule = $user->getApiRateLimit() ?: $this->rateLimitRules->getDefaultUserRule();
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
            $sunInfo = date_sun_info($time, $lat, $lng) ?: [];
            $normalized['closestSunset'] = is_int($sunInfo['sunset'] ?? null) ? $sunInfo['sunset'] : null;
            $normalized['closestSunrise'] = is_int($sunInfo['sunrise'] ?? null) ? $sunInfo['sunrise'] : null;
        }
        if ($context['accessToken'] ?? null) {
            $normalized['accessToken'] = $this->normalizer->normalize($context['accessToken'], context: $context);
        }
    }

    public function supportsNormalization($entity, $format = null) {
        return $entity instanceof User;
    }
}
