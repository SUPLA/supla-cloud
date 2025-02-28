<?php

namespace SuplaBundle\Model\UserConfigTranslator;

use OpenApi\Annotations as OA;
use SuplaBundle\Entity\HasUserConfig;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Utils\NumberUtils;

/**
 * @OA\Schema(schema="ChannelConfigThermometer", description="Config for `THERMOMETER`",
 *   @OA\Property(property="temperatureAdjustment", type="number"),
 *   @OA\Property(property="minTemperatureAdjustment", type="number"),
 *   @OA\Property(property="maxTemperatureAdjustment", type="number"),
 * )
 * @OA\Schema(schema="ChannelConfigHumidityAndThermometer", description="Config for `HUMIDITYANDTEMPERATURE`",
 *   @OA\Property(property="temperatureAdjustment", type="number"),
 *   @OA\Property(property="humidityAdjustment", type="number"),
 *   @OA\Property(property="minTemperatureAdjustment", type="number"),
 *   @OA\Property(property="maxTemperatureAdjustment", type="number"),
 *   @OA\Property(property="minHumidityAdjustment", type="number"),
 *   @OA\Property(property="maxHumidityAdjustment", type="number"),
 * )
 */
class TemperatureAdjustmentConfigTranslator extends UserConfigTranslator {
    use FixedRangeParamsTranslator;

    public function getConfig(HasUserConfig $subject): array {
        return [
            'temperatureAdjustment' => NumberUtils::maximumDecimalPrecision($subject->getUserConfigValue('temperatureAdjustment', 0) / 100),
            'minTemperatureAdjustment' => NumberUtils::maximumDecimalPrecision($subject->getProperty('minTemperatureAdjustment', -1000) / 100),
            'maxTemperatureAdjustment' => NumberUtils::maximumDecimalPrecision($subject->getProperty('maxTemperatureAdjustment', 1000) / 100),
        ];
    }

    public function setConfig(HasUserConfig $subject, array $config) {
        $currentConfig = $this->getConfig($subject);
        if (array_key_exists('temperatureAdjustment', $config)) {
            $adjustment = $this->getValueInRange(
                $config['temperatureAdjustment'],
                $currentConfig['minTemperatureAdjustment'],
                $currentConfig['maxTemperatureAdjustment'],
                0
            );
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
