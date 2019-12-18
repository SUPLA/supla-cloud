<?php

namespace SuplaBundle\Model\ChannelParamsUpdater\ChannelParamsConfig;

use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;

class RelayTimeChannelParamTranslator implements ChannelParamTranslator {
    public function getConfigFromParams(IODeviceChannel $channel): array {
        return ['relayTime' => $channel->getParam1()];
    }

    public function setParamsFromConfig(array $config, IODeviceChannel $channel) {
        $channel->setParam1($config['relayTime'] ?? $channel->getParam1());
    }

    public function supports(IODeviceChannel $channel): bool {
        return in_array($channel->getFunction()->getId(), [
            ChannelFunction::CONTROLLINGTHEDOORLOCK,
            ChannelFunction::CONTROLLINGTHEGARAGEDOOR,
            ChannelFunction::CONTROLLINGTHEGATE,
            ChannelFunction::CONTROLLINGTHEGATEWAYLOCK,
            ChannelFunction::CONTROLLINGTHEROLLERSHUTTER,
            ChannelFunction::STAIRCASETIMER,
        ]);
    }
}
