<?php

namespace SuplaBundle\Model\ChannelParamsTranslator;

use OpenApi\Annotations as OA;
use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Utils\NumberUtils;

/**
 * @OA\Schema(schema="ChannelConfigThermometer", description="Config for `THERMOMETER`",
 *   @OA\Property(property="temperatureAdjustment", type="number", minimum=-10, maximum=10),
 * )
 * @OA\Schema(schema="ChannelConfigHumidityAndThermometer", description="Config for `HUMIDITYANDTEMPERATURE`",
 *   @OA\Property(property="temperatureAdjustment", type="number", minimum=-10, maximum=10),
 *   @OA\Property(property="humidityAdjustment", type="number", minimum=-10, maximum=10),
 * )
 */
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
