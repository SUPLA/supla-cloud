<?php

namespace SuplaBundle\Model\UserConfigTranslator;

use SuplaBundle\Entity\HasUserConfig;
use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Enums\ChannelFlags;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\RgbwCommand;

class RgbwUserConfigTranslator extends UserConfigTranslator {
    public function getConfig(HasUserConfig $subject): array {
        $config = [];
        if ($subject instanceof IODeviceChannel) {
            $config['availableRgbwCommands'] = [];
            if (ChannelFlags::RGBW_COMMANDS_SUPPORTED()->isSupported($subject->getFlags())) {
                $rgbwCommands = RgbwCommand::forFunction($subject->getFunction()->getId());
                $config['availableRgbwCommands'] = array_map(fn(RgbwCommand $c) => $c->name, $rgbwCommands);
            }
        }
        return $config;
    }

    public function setConfig(HasUserConfig $subject, array $config) {
    }

    public function supports(HasUserConfig $subject): bool {
        return in_array($subject->getFunction()->getId(), [
            ChannelFunction::RGBLIGHTING,
            ChannelFunction::DIMMERANDRGBLIGHTING,
            ChannelFunction::DIMMER,
            ChannelFunction::DIMMER_CCT,
            ChannelFunction::DIMMER_CCT_AND_RGB,
        ]);
    }
}
