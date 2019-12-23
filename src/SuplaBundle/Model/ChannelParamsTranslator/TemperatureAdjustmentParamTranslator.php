<?php

namespace SuplaBundle\Model\ChannelParamsTranslator;

use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Utils\NumberUtils;

class TemperatureAdjustmentParamTranslator implements ChannelParamTranslator {
    use FixedRangeParamsTranslator;

    public function getConfigFromParams(IODeviceChannel $channel): array {
        return [
            'temperatureAdjustment' => NumberUtils::maximumDecimalPrecision($channel->getParam2() / 100, 2),
        ];
    }

    public function setParamsFromConfig(IODeviceChannel $channel, array $config) {
        if (array_key_exists('temperatureAdjustment', $config)) {
            $channel->setParam2(intval($this->getValueInRange($config['temperatureAdjustment'], -10, 10) * 100));
        }
    }

    public function supports(IODeviceChannel $channel): bool {
        return in_array($channel->getFunction()->getId(), [
            ChannelFunction::THERMOMETER,
            ChannelFunction::HUMIDITYANDTEMPERATURE,
        ]);
    }
}
