<?php

namespace SuplaBundle\Model\UserConfigTranslator;

use OpenApi\Annotations as OA;
use SuplaBundle\Entity\HasUserConfig;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Utils\NumberUtils;

/**
 * @OA\Schema(schema="ChannelConfigHumidity", description="Config for `HUMIDITY`",
 *   @OA\Property(property="humidityAdjustment", type="number"),
 *   @OA\Property(property="minHumidityAdjustment", type="number"),
 *   @OA\Property(property="maxHumidityAdjustment", type="number"),
 * )
 */
class HumidityAdjustmentConfigTranslator extends UserConfigTranslator {
    use FixedRangeParamsTranslator;

    public function getConfig(HasUserConfig $subject): array {
        return [
            'humidityAdjustment' => NumberUtils::maximumDecimalPrecision($subject->getUserConfigValue('humidityAdjustment', 0) / 100),
            'minHumidityAdjustment' => NumberUtils::maximumDecimalPrecision($subject->getProperty('minHumidityAdjustment', -1000) / 100),
            'maxHumidityAdjustment' => NumberUtils::maximumDecimalPrecision($subject->getProperty('maxHumidityAdjustment', 1000) / 100),
        ];
    }

    public function setConfig(HasUserConfig $subject, array $config) {
        $currentConfig = $this->getConfig($subject);
        if (array_key_exists('humidityAdjustment', $config)) {
            $adjustment = $this->getValueInRange(
                $config['humidityAdjustment'],
                $currentConfig['minHumidityAdjustment'],
                $currentConfig['maxHumidityAdjustment'],
                0
            );
            $subject->setUserConfigValue('humidityAdjustment', round($adjustment * 100));
        }
    }

    public function supports(HasUserConfig $subject): bool {
        return in_array($subject->getFunction()->getId(), [
            ChannelFunction::HUMIDITY,
            ChannelFunction::HUMIDITYANDTEMPERATURE,
        ]);
    }
}
