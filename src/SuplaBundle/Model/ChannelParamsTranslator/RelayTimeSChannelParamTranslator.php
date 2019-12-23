<?php

namespace SuplaBundle\Model\ChannelParamsTranslator;

use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Utils\NumberUtils;

class RelayTimeSChannelParamTranslator implements ChannelParamTranslator {
    use FixedRangeParamsTranslator;

    public function getConfigFromParams(IODeviceChannel $channel): array {
        return ['relayTimeS' => NumberUtils::maximumDecimalPrecision($channel->getParam1() / 10, 1)];
    }

    public function setParamsFromConfig(IODeviceChannel $channel, array $config) {
        if (isset($config['relayTimeS'])) {
            $channel->setParam1(intval($this->getValueInRange($config['relayTimeS'], 0, 3600) * 10));
        }
    }

    public function supports(IODeviceChannel $channel): bool {
        return in_array($channel->getFunction()->getId(), [
            ChannelFunction::STAIRCASETIMER,
        ]);
    }
}
