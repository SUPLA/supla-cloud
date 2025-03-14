<?php

namespace SuplaBundle\Model\UserConfigTranslator;

use SuplaBundle\Entity\HasUserConfig;
use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Enums\ChannelFlags;
use SuplaBundle\Enums\ChannelFunction;

class BatteryPoweredUserConfigTranslator extends UserConfigTranslator {
    public function getConfig(HasUserConfig $subject): array {
        if ($subject instanceof IODeviceChannel) {
            return [
                'isBatteryCoverAvailable' => ChannelFlags::BATTERY_COVER_AVAILABLE()->isSupported($subject->getFlags()),
                'isBatteryPowered' => $subject->getLastKnownChannelState()['batteryPowered'] ?? false,
            ];
        } else {
            return [];
        }
    }

    public function setConfig(HasUserConfig $subject, array $config) {
    }

    public function supports(HasUserConfig $subject): bool {
        return in_array($subject->getFunction()->getId(), [
            ChannelFunction::THERMOMETER,
            ChannelFunction::HUMIDITYANDTEMPERATURE,
            ChannelFunction::HVAC_THERMOSTAT,
            ChannelFunction::HVAC_THERMOSTAT_HEAT_COOL,
            ChannelFunction::HVAC_THERMOSTAT_DIFFERENTIAL,
            ChannelFunction::HVAC_DOMESTIC_HOT_WATER,
        ]);
    }
}
