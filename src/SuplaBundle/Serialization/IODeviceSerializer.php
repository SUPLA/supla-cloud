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

use SuplaBundle\Entity\Main\IODevice;
use SuplaBundle\Model\ApiVersions;
use SuplaBundle\Model\CurrentUserAware;
use SuplaBundle\Model\Schedule\ScheduleManager;
use SuplaBundle\Model\TimeProvider;
use SuplaBundle\Model\UserConfigTranslator\IODeviceConfigTranslator;
use SuplaBundle\Repository\IODeviceRepository;
use SuplaBundle\Supla\SuplaServerAware;
use SuplaBundle\Utils\JsonArrayObject;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

class IODeviceSerializer extends AbstractSerializer implements NormalizerAwareInterface {
    use SuplaServerAware;
    use CurrentUserAware;
    use NormalizerAwareTrait;

    private ScheduleManager $scheduleManager;
    private IODeviceRepository $iodeviceRepository;
    private IODeviceConfigTranslator $configTranslator;
    private TimeProvider $timeProvider;

    public function __construct(
        ScheduleManager $scheduleManager,
        IODeviceRepository $iodeviceRepository,
        IODeviceConfigTranslator $configTranslator,
        TimeProvider $timeProvider
    ) {
        parent::__construct();
        $this->scheduleManager = $scheduleManager;
        $this->iodeviceRepository = $iodeviceRepository;
        $this->configTranslator = $configTranslator;
        $this->timeProvider = $timeProvider;
    }

    /**
     * @param \SuplaBundle\Entity\Main\IODevice $ioDevice
     * @inheritdoc
     */
    protected function addExtraFields(array &$normalized, $ioDevice, array $context) {
        $normalized['locationId'] = $ioDevice->getLocation()->getId();
        $normalized['originalLocationId'] = $ioDevice->getOriginalLocation() ? $ioDevice->getOriginalLocation()->getId() : null;
        if ($this->isSerializationGroupRequested('connected', $context)) {
            $normalized['connected'] = $this->suplaServer->isDeviceConnected($ioDevice);
        }
        if ($this->isSerializationGroupRequested('iodevice.schedules', $context)) {
            $normalized['schedules'] = $this->serializeSchedules($ioDevice, $context);
        }
        if (ApiVersions::V2_4()->isRequestedEqualOrGreaterThan($context)) {
            if (!isset($normalized['relationsCount'])) {
                if ($this->isSerializationGroupRequested('iodevice.relationsCount', $context)) {
                    $normalized['relationsCount'] = $this->iodeviceRepository->find($ioDevice->getId())->getRelationsCount();
                }
            }
            $normalized['config'] = new JsonArrayObject($this->configTranslator->getConfig($ioDevice));
        } else {
            $normalized['channelsIds'] = $this->toIds($ioDevice->getChannels());
            $normalized['regIpv4'] = ($normalized['regIpv4'] ?? null) ? ip2long($normalized['regIpv4']) : null;
            $normalized['lastIpv4'] = ($normalized['lastIpv4'] ?? null) ? ip2long($normalized['lastIpv4']) : null;
        }
        if ($ioDevice->isLocked()) {
            $normalized['locked'] = true;
        }
        if (array_key_exists('pairingResult', $normalized) && $normalized['pairingResult']) {
            $pr = &$normalized['pairingResult'];
            $dates = array_values(array_filter([$pr['timeoutAt'] ?? null, $pr['startedAt'] ?? null, $pr['time'] ?? null]));
            if (count($dates)) {
                $theDate = $dates[0];
                $pr['timedOut'] = strtotime($theDate) < $this->timeProvider->getTimestamp('-1 minute');
            }
        }
    }

    private function serializeSchedules(IODevice $ioDevice, array $context = []): array {
        $schedules = $this->scheduleManager->findSchedulesForDevice($ioDevice);
        return $this->normalizer->normalize($schedules, null, $context);
    }

    public function supportsNormalization($entity, $format = null) {
        return $entity instanceof IODevice;
    }
}
