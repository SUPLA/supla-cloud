<?php

namespace SuplaBundle\Model\UserConfigTranslator;

use OpenApi\Annotations as OA;
use SuplaBundle\Entity\HasUserConfig;
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
class TemperatureAdjustmentParamTranslator implements UserConfigTranslator {
    use FixedRangeParamsTranslator;

    public function getConfig(HasUserConfig $subject): array {
        return [
            'temperatureAdjustment' => NumberUtils::maximumDecimalPrecision($subject->getParam2() / 100, 2),
        ];
    }

    public function setConfig(HasUserConfig $subject, array $config) {
        if (array_key_exists('temperatureAdjustment', $config)) {
            $subject->setParam2(intval($this->getValueInRange($config['temperatureAdjustment'], -10, 10) * 100));
        }
    }

    public function supports(HasUserConfig $subject): bool {
        return in_array($subject->getFunction()->getId(), [
            ChannelFunction::THERMOMETER,
            ChannelFunction::HUMIDITYANDTEMPERATURE,
        ]);
    }
}
