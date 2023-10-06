<?php

namespace SuplaBundle\Model\UserConfigTranslator;

use OpenApi\Annotations as OA;
use SuplaBundle\Entity\HasUserConfig;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Utils\NumberUtils;

/**
 * @OA\Schema(schema="ChannelConfigThermometer", description="Config for `THERMOMETER`",
 *   @OA\Property(property="temperatureAdjustment", type="number", minimum=-1000, maximum=1000),
 * )
 * @OA\Schema(schema="ChannelConfigHumidityAndThermometer", description="Config for `HUMIDITYANDTEMPERATURE`",
 *   @OA\Property(property="temperatureAdjustment", type="number", minimum=-1000, maximum=1000),
 *   @OA\Property(property="humidityAdjustment", type="number", minimum=-1000, maximum=1000),
 * )
 */
class TemperatureAdjustmentParamTranslator extends UserConfigTranslator {
    use FixedRangeParamsTranslator;

    public function getConfig(HasUserConfig $subject): array {
        return [
            'temperatureAdjustment' => NumberUtils::maximumDecimalPrecision($subject->getUserConfigValue('temperatureAdjustment', 0) / 100),
        ];
    }

    public function setConfig(HasUserConfig $subject, array $config) {
        if (array_key_exists('temperatureAdjustment', $config)) {
            $adjustment = $this->getValueInRange($config['temperatureAdjustment'], -1000, 1000, 0);
            $subject->setUserConfigValue('temperatureAdjustment', round($adjustment * 100));
        }
    }

    public function supports(HasUserConfig $subject): bool {
        return in_array($subject->getFunction()->getId(), [
            ChannelFunction::THERMOMETER,
            ChannelFunction::HUMIDITYANDTEMPERATURE,
        ]);
    }
}
