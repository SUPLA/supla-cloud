<?php

namespace SuplaBundle\Model\ChannelParamsUpdater\ChannelParamsConfig;

use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;

class OpeningSensorSecondaryParamTranslator implements ChannelParamTranslator {
    public function getConfigFromParams(IODeviceChannel $channel): array {
        return ['openingSensorSecondaryChannelId' => $channel->getParam3()];
    }

    public function setParamsFromConfig(IODeviceChannel $channel, array $config) {
        $channel->setParam3($config['openingSensorSecondaryChannelId'] ?? $channel->getParam3());
    }

    public function supports(IODeviceChannel $channel): bool {
        return in_array($channel->getFunction()->getId(), [
            ChannelFunction::CONTROLLINGTHEGATE,
        ]);
    }
}
