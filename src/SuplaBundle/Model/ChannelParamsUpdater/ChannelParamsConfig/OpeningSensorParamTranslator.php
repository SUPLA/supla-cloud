<?php

namespace SuplaBundle\Model\ChannelParamsUpdater\ChannelParamsConfig;

use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;

class OpeningSensorParamTranslator implements ChannelParamTranslator {
    public function getConfigFromParams(IODeviceChannel $channel): array {
        return ['openingSensorChannelId' => $channel->getParam2()];
    }

    public function setParamsFromConfig(array $config, IODeviceChannel $channel) {
        $channel->setParam2($config['openingSensorChannelId'] ?? $channel->getParam2());
    }

    public function supports(IODeviceChannel $channel): bool {
        return in_array($channel->getFunction()->getId(), [
            ChannelFunction::CONTROLLINGTHEDOORLOCK,
            ChannelFunction::CONTROLLINGTHEGARAGEDOOR,
            ChannelFunction::CONTROLLINGTHEGATE,
            ChannelFunction::CONTROLLINGTHEGATEWAYLOCK,
            ChannelFunction::CONTROLLINGTHEROLLERSHUTTER,
        ]);
    }
}
