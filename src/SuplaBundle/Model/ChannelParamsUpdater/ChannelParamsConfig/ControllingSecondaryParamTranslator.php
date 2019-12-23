<?php

namespace SuplaBundle\Model\ChannelParamsUpdater\ChannelParamsConfig;

use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;

class ControllingSecondaryParamTranslator implements ChannelParamTranslator {
    public function getConfigFromParams(IODeviceChannel $channel): array {
        return ['controllingSecondaryChannelId' => $channel->getParam2()];
    }

    public function setParamsFromConfig(IODeviceChannel $channel, array $config) {
        $channel->setParam2($config['controllingSecondaryChannelId'] ?? $channel->getParam2());
    }

    public function supports(IODeviceChannel $channel): bool {
        return in_array($channel->getFunction()->getId(), [
            ChannelFunction::OPENINGSENSOR_GATE,
        ]);
    }
}
