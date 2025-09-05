<?php

namespace SuplaBundle\Model\UserConfigTranslator;

use SuplaBundle\Entity\HasUserConfig;
use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Enums\ChannelFlags;

class BatteryUserConfigTranslator extends UserConfigTranslator {
    public function getConfig(HasUserConfig $subject): array {
        if ($subject instanceof IODeviceChannel) {
            return [
                'isBatteryCoverAvailable' => ChannelFlags::BATTERY_COVER_AVAILABLE()->isSupported($subject->getFlags()),
                'isBatteryAvailable' => array_key_exists('batteryLevel', $subject->getLastKnownChannelState()),
            ];
        } else {
            return [];
        }
    }

    public function setConfig(HasUserConfig $subject, array $config) {
    }

    public function supports(HasUserConfig $subject): bool {
        return true;
    }
}
