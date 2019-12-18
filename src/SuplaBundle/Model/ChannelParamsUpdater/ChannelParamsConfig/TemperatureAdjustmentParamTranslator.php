<?php

namespace SuplaBundle\Model\ChannelParamsUpdater\ChannelParamsConfig;

use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;

class TemperatureAdjustmentParamTranslator implements ChannelParamTranslator {
    public function getConfigFromParams(IODeviceChannel $channel): array {
        return [
            'temperatureAdjustment' => $channel->getParam2(),
        ];
    }

    public function setParamsFromConfig(array $config, IODeviceChannel $channel) {
        $channel->setParam2($config['temperatureAdjustment'] ?? $channel->getParam2());
    }

    public function supports(IODeviceChannel $channel): bool {
        return in_array($channel->getFunction()->getId(), [
            ChannelFunction::THERMOMETER,
            ChannelFunction::HUMIDITYANDTEMPERATURE,
        ]);
    }
}
