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

namespace SuplaApiBundle\Serialization;

use Assert\Assertion;
use SuplaApiBundle\Model\CurrentUserAware;
use SuplaBundle\Entity\IODevice;
use SuplaBundle\Model\Schedule\ScheduleManager;
use SuplaBundle\Supla\SuplaServerAware;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

class IODeviceSerializer extends AbstractSerializer implements NormalizerAwareInterface {
    use SuplaServerAware;
    use CurrentUserAware;
    use NormalizerAwareTrait;

    /** @var ScheduleManager */
    private $scheduleManager;

    public function __construct(ScheduleManager $scheduleManager) {
        $this->scheduleManager = $scheduleManager;
    }

    /**
     * @param IODevice $ioDevice
     * @inheritdoc
     */
    public function normalize($ioDevice, $format = null, array $context = []) {
        $normalized = parent::normalize($ioDevice, $format, $context);
        if (isset($context[self::GROUPS]) && is_array($context[self::GROUPS])) {
            if (in_array('connected', $context[self::GROUPS])) {
                $normalized['connected'] = $this->isDeviceConnected($ioDevice);
            }
            if (in_array('schedules', $context[self::GROUPS])) {
                $normalized['schedules'] = $this->findSchedulesForDevice($ioDevice, $format, $context);
            }
        }
        return $normalized;
    }

    public function supportsNormalization($entity, $format = null) {
        return $entity instanceof IODevice;
    }

    private function isDeviceConnected(IODevice $ioDevice): bool {
        if (!$ioDevice->getEnabled()) {
            return false;
        }
        $user = $this->getCurrentUser();
        Assertion::notNull($user, 'User not authenticated');
        $connectedIds = $this->suplaServer->checkDevicesConnection($user->getId(), [$ioDevice->getId()]);
        return in_array($ioDevice->getId(), $connectedIds);
    }

    private function findSchedulesForDevice(IODevice $ioDevice, $format = null, array $context = []): array {
        $schedules = $this->scheduleManager->findSchedulesForDevice($ioDevice);
        return $this->normalizer->normalize($schedules, $format, $context);
    }
}
