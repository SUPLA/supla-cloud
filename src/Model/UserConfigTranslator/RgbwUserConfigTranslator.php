<?php

namespace App\Model\UserConfigTranslator;

use App\Entity\HasUserConfig;
use App\Entity\Main\IODeviceChannel;
use App\Enums\ChannelFlags;
use App\Enums\ChannelFunction;
use App\Enums\RgbwCommand;

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
