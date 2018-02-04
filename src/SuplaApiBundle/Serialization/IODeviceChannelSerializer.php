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

use SuplaApiBundle\Model\CurrentUserAware;
use SuplaBundle\Entity\IODevice;
use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Supla\SuplaConst;
use SuplaBundle\Supla\SuplaServerAware;

class IODeviceChannelSerializer extends AbstractSerializer {
    use CurrentUserAware;
    use SuplaServerAware;

    /**
     * @param IODeviceChannel $channel
     * @inheritdoc
     */
    public function normalize($channel, $format = null, array $context = []) {
        $normalized = parent::normalize($channel, $format, $context);
        if (is_array($normalized)) {
            $normalized['iodeviceId'] = $channel->getIoDevice()->getId();
            $normalized['locationId'] = $channel->getLocation()->getId();
            $normalized['functionId'] = $channel->getFunction()->getId();
            $normalized['typeId'] = $channel->getType()->getId();
            if (in_array('connected', $context[self::GROUPS])) {
                $normalized['connected'] = $this->isDeviceConnected($channel->getIoDevice());
            }
            if (in_array('state', $context[self::GROUPS])) {
                $normalized['state'] = $this->getChannelStatus($channel);
            }
        }
        return $normalized;
    }

    private function isDeviceConnected(IODevice $ioDevice): bool {
        if (!$ioDevice->getEnabled()) {
            return false;
        }
        $user = $this->getCurrentUserOrThrow();
        $connectedIds = $this->suplaServer->checkDevicesConnection($user->getId(), [$ioDevice->getId()]);
        return in_array($ioDevice->getId(), $connectedIds);
    }

    private function getChannelStatus(IODeviceChannel $channel): array {
        $result = [];
        $func = $channel->getFunction()->getId();
        $channelid = $channel->getId();
        $devid = $channel->getIoDevice()->getId();
        $userid = $this->getCurrentUserOrThrow()->getId();
        // TODO refactor
        switch ($func) {
            case SuplaConst::FNC_POWERSWITCH:
            case SuplaConst::FNC_LIGHTSWITCH:
                $value = $this->suplaServer->getCharValue($userid, $devid, $channelid);
                $result['on'] = $value == '1' ? true : false;

                break;

            case SuplaConst::FNC_OPENINGSENSOR_GATEWAY:
            case SuplaConst::FNC_OPENINGSENSOR_GATE:
            case SuplaConst::FNC_OPENINGSENSOR_GARAGEDOOR:
            case SuplaConst::FNC_NOLIQUIDSENSOR:
            case SuplaConst::FNC_OPENINGSENSOR_DOOR:
            case SuplaConst::FNC_OPENINGSENSOR_ROLLERSHUTTER:
                $value = $this->suplaServer->getCharValue($userid, $devid, $channelid);
                $result['hi'] = $value == '1' ? true : false;

                break;

            case SuplaConst::FNC_THERMOMETER:
            case SuplaConst::FNC_HUMIDITY:
            case SuplaConst::FNC_HUMIDITYANDTEMPERATURE:
                if ($func == SuplaConst::FNC_THERMOMETER
                    || $func == SuplaConst::FNC_HUMIDITYANDTEMPERATURE
                ) {
                    $value = $this->suplaServer->getTemperatureValue($userid, $devid, $channelid);

                    if ($value !== false) {
                        $result['temperature'] = $value;
                    }
                }

                if ($func == SuplaConst::FNC_HUMIDITY
                    || $func == SuplaConst::FNC_HUMIDITYANDTEMPERATURE
                ) {
                    $value = $this->suplaServer->getHumidityValue($userid, $devid, $channelid);

                    if ($value !== false) {
                        $result['humidity'] = $value;
                    }
                }

                break;

            case SuplaConst::FNC_DIMMER:
            case SuplaConst::FNC_RGBLIGHTING:
            case SuplaConst::FNC_DIMMERANDRGBLIGHTING:
                $value = $this->suplaServer->getRgbwValue($userid, $devid, $channelid);

                if ($value !== false) {
                    if ($func == SuplaConst::FNC_RGBLIGHTING
                        || $func == SuplaConst::FNC_DIMMERANDRGBLIGHTING
                    ) {
                        $result['color'] = $value['color'];
                        $result['color_brightness'] = $value['color_brightness'];
                    }

                    if ($func == SuplaConst::FNC_DIMMER
                        || $func == SuplaConst::FNC_DIMMERANDRGBLIGHTING
                    ) {
                        $result['brightness'] = $value['brightness'];
                    }
                }

                break;

            case SuplaConst::FNC_DISTANCESENSOR:
                $value = $this->suplaServer->getDistanceValue($userid, $devid, $channelid);

                if ($value !== false) {
                    $result['distance'] = $value;
                }

                break;

            case SuplaConst::FNC_DEPTHSENSOR:
                $value = $this->suplaServer->getDistanceValue($userid, $devid, $channelid);

                if ($value !== false) {
                    $result['depth'] = $value;
                }

                break;
        }
        return $result;
    }

    public function supportsNormalization($entity, $format = null) {
        return $entity instanceof IODeviceChannel;
    }
}
