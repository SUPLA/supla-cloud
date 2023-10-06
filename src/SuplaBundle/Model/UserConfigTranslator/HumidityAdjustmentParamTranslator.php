<?php

namespace SuplaBundle\Model\UserConfigTranslator;

use OpenApi\Annotations as OA;
use SuplaBundle\Entity\HasUserConfig;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Utils\NumberUtils;

/**
 * @OA\Schema(schema="ChannelConfigHumidity", description="Config for `HUMIDITY`",
 *   @OA\Property(property="humidityAdjustment", type="number", minimum=-1000, maximum=1000),
 * )
 */
class HumidityAdjustmentParamTranslator extends UserConfigTranslator {
    use FixedRangeParamsTranslator;

    public function getConfig(HasUserConfig $subject): array {
        return [
            'humidityAdjustment' => NumberUtils::maximumDecimalPrecision($subject->getUserConfigValue('humidityAdjustment', 0) / 100),
        ];
    }

    public function setConfig(HasUserConfig $subject, array $config) {
        if (array_key_exists('humidityAdjustment', $config)) {
            $adjustment = $this->getValueInRange($config['humidityAdjustment'], -1000, 1000, 0);
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
