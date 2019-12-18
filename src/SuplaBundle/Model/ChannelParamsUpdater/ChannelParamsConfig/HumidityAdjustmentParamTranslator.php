<?php

namespace SuplaBundle\Model\ChannelParamsUpdater\ChannelParamsConfig;

use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;

class HumidityAdjustmentParamTranslator implements ChannelParamTranslator {
    public function getConfigFromParams(IODeviceChannel $channel): array {
        return [
            'humidityAdjustment' => $channel->getParam3(),
        ];
    }

    public function setParamsFromConfig(array $config, IODeviceChannel $channel) {
        $channel->setParam3($config['humidityAdjustment'] ?? $channel->getParam3());
    }

    public function supports(IODeviceChannel $channel): bool {
        return in_array($channel->getFunction()->getId(), [
            ChannelFunction::HUMIDITY,
            ChannelFunction::HUMIDITYANDTEMPERATURE,
        ]);
    }
}
