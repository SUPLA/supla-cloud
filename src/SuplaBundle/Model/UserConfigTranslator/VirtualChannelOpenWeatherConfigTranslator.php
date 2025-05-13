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

namespace SuplaBundle\Model\UserConfigTranslator;

use Assert\Assertion;
use SuplaBundle\Entity\HasUserConfig;
use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Enums\ChannelType;
use SuplaBundle\Enums\VirtualChannelType;
use SuplaBundle\Model\VirtualChannelStateUpdater;

class VirtualChannelOpenWeatherConfigTranslator extends UserConfigTranslator {
    use FixedRangeParamsTranslator;

    public function __construct(private VirtualChannelStateUpdater $virtualChannelStateUpdater) {
    }

    public function getConfig(HasUserConfig $subject): array {
        return [
            'virtualChannelType' => VirtualChannelType::OPEN_WEATHER,
            'hiddenConfigFields' => [
                'valueDivider',
                'valueMultiplier',
                'valueAdded',
                'keepHistory',
            ],
        ];
    }

    public function setConfig(HasUserConfig $subject, array $config) {
        if (array_key_exists('cityId', $config)) {
            if ($config['cityId']) {
                Assertion::integer($config['cityId']);
                $subject->setUserConfigValue('cityId', $config['cityId']);
            } else {
                $subject->setUserConfigValue('cityId', null);
            }
        }
        if (array_key_exists('weatherField', $config)) {
            if ($config['weatherField']) {
                Assertion::inArray($config['weatherField'], ['temp']);
                $subject->setUserConfigValue('weatherField', $config['weatherField']);
            } else {
                $subject->setUserConfigValue('weatherField', null);
            }
        }
        $this->virtualChannelStateUpdater->updateChannels([$subject]);
    }

    public function supports(HasUserConfig $subject): bool {
        return $subject instanceof IODeviceChannel && $subject->getType()->getId() === ChannelType::VIRTUAL
            && $subject->getProperty('virtualChannelType') === VirtualChannelType::OPEN_WEATHER;
    }
}
