<?php

namespace SuplaBundle\Model\UserConfigTranslator;

use SuplaBundle\Entity\HasUserConfig;
use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Enums\ChannelFlags;

class SubdeviceUserConfigTranslator extends UserConfigTranslator {
    public function getConfig(HasUserConfig $subject): array {
        return [
            'identifySubdeviceAvailable' => ChannelFlags::IDENTIFY_SUBDEVICE_AVAILABLE()->isOn($subject->getFlags()),
            'restartSubdeviceAvailable' => ChannelFlags::RESTART_SUBDEVICE_AVAILABLE()->isOn($subject->getFlags()),
        ];
    }

    public function setConfig(HasUserConfig $subject, array $config) {
    }

    public function supports(HasUserConfig $subject): bool {
        return $subject instanceof IODeviceChannel && $subject->getSubDeviceId() > 0;
    }
}
