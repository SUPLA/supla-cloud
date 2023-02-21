<?php

namespace SuplaBundle\Model\UserConfigTranslator;

use OpenApi\Annotations as OA;
use SuplaBundle\Entity\HasUserConfig;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Utils\NumberUtils;

/**
 * @OA\Schema(schema="ChannelConfigHumidity", description="Config for `HUMIDITY`",
 *   @OA\Property(property="humidityAdjustment", type="number", minimum=-10, maximum=10),
 * )
 */
class HumidityAdjustmentParamTranslator implements UserConfigTranslator {
    use FixedRangeParamsTranslator;

    public function getConfig(HasUserConfig $subject): array {
        return [
            'humidityAdjustment' => NumberUtils::maximumDecimalPrecision($subject->getParam3() / 100, 2),
        ];
    }

    public function setConfig(HasUserConfig $subject, array $config) {
        if (array_key_exists('humidityAdjustment', $config)) {
            $subject->setParam3(intval($this->getValueInRange($config['humidityAdjustment'], -10, 10) * 100));
        }
    }

    public function supports(HasUserConfig $subject): bool {
        return in_array($subject->getFunction()->getId(), [
            ChannelFunction::HUMIDITY,
            ChannelFunction::HUMIDITYANDTEMPERATURE,
        ]);
    }
}
