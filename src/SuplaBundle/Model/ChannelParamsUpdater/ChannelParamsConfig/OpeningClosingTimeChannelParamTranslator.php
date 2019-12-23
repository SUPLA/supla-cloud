<?php

namespace SuplaBundle\Model\ChannelParamsUpdater\ChannelParamsConfig;

use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Utils\NumberUtils;

class OpeningClosingTimeChannelParamTranslator implements ChannelParamTranslator {
    public function getConfigFromParams(IODeviceChannel $channel): array {
        return [
            'openingTimeS' => NumberUtils::maximumDecimalPrecision($channel->getParam1() / 10, 1),
            'closingTimeS' => NumberUtils::maximumDecimalPrecision($channel->getParam3() / 10, 1),
        ];
    }

    public function setParamsFromConfig(IODeviceChannel $channel, array $config) {
        if (isset($config['openingTimeS'])) {
            $channel->setParam1(intval($config['openingTimeS'] * 10));
        }
        if (isset($config['closingTimeS'])) {
            $channel->setParam3(intval($config['closingTimeS'] * 10));
        }
    }

    public function supports(IODeviceChannel $channel): bool {
        return in_array($channel->getFunction()->getId(), [
            ChannelFunction::CONTROLLINGTHEROLLERSHUTTER,
        ]);
    }
}
