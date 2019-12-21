<?php

namespace SuplaBundle\Model\ChannelParamsUpdater\ChannelParamsConfig;

use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Utils\NumberUtils;

class RelayTimeSChannelParamTranslator implements ChannelParamTranslator {
    public function getConfigFromParams(IODeviceChannel $channel): array {
        return ['relayTimeS' => NumberUtils::maximumDecimalPrecision($channel->getParam1() / 10, 1)];
    }

    public function setParamsFromConfig(array $config, IODeviceChannel $channel) {
        if (isset($config['relayTimeS'])) {
            $channel->setParam1(intval($config['relayTimeS'] * 10));
        }
    }

    public function supports(IODeviceChannel $channel): bool {
        return in_array($channel->getFunction()->getId(), [
            ChannelFunction::STAIRCASETIMER,
        ]);
    }
}
