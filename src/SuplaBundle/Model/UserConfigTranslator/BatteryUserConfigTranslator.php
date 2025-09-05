<?php

namespace SuplaBundle\Model\UserConfigTranslator;

use SuplaBundle\Entity\HasUserConfig;
use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Enums\ChannelFlags;

class BatteryUserConfigTranslator extends UserConfigTranslator {
    public function getConfig(HasUserConfig $subject): array {
        $config = [];
        if ($subject instanceof IODeviceChannel) {
            if (ChannelFlags::BATTERY_COVER_AVAILABLE()->isSupported($subject->getFlags())) {
                $config['isBatteryCoverAvailable'] = ChannelFlags::BATTERY_COVER_AVAILABLE()->isOn($subject->getFlags());
            }
            if (ChannelFlags::HAS_EXTENDED_CHANNEL_STATE()->isSupported($subject->getFlags())) {
                $config['isBatteryAvailable'] = array_key_exists('batteryLevel', $subject->getLastKnownChannelState());
            }
        }
        return $config;
    }

    public function setConfig(HasUserConfig $subject, array $config) {
    }

    public function supports(HasUserConfig $subject): bool {
        return true;
    }
}
