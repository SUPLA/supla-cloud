<?php

namespace SuplaBundle\Model\ChannelParamsTranslator;

use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;

class RollerShutterConfigTranslator implements ChannelParamTranslator {
    public function getConfigFromParams(IODeviceChannel $channel): array {
        return [
            'stepByStepSupport' => $channel->getProperties()['stepByStepSupport'] ?? false,
        ];
    }

    public function setParamsFromConfig(IODeviceChannel $channel, array $config) {
    }

    public function supports(IODeviceChannel $channel): bool {
        return in_array($channel->getFunction()->getId(), [
            ChannelFunction::CONTROLLINGTHEROLLERSHUTTER,
        ]);
    }
}
