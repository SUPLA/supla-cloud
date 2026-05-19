<?php

namespace App\Model\UserConfigTranslator;

use App\Entity\HasUserConfig;
use App\Entity\Main\IODeviceChannel;

class HideLockedDevicesInChannelsListConfigTranslator extends UserConfigTranslator {
    public function getConfig(HasUserConfig $subject): array {
        return [
            'hideInChannelsList' => true,
        ];
    }

    public function setConfig(HasUserConfig $subject, array $config) {
    }

    public function supports(HasUserConfig $subject): bool {
        return $subject instanceof IODeviceChannel && $subject->getIoDevice() && $subject->getIoDevice()->isLocked();
    }
}
