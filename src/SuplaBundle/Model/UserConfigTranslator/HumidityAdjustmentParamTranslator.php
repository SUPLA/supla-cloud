<?php

namespace SuplaBundle\Model\UserConfigTranslator;

use OpenApi\Annotations as OA;
use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Utils\NumberUtils;

/**
 * @OA\Schema(schema="ChannelConfigHumidity", description="Config for `HUMIDITY`",
 *   @OA\Property(property="humidityAdjustment", type="number", minimum=-10, maximum=10),
 * )
 */
class HumidityAdjustmentParamTranslator implements ChannelParamTranslator {
    use FixedRangeParamsTranslator;

    public function getConfigFromParams(IODeviceChannel $channel): array {
        return [
            'humidityAdjustment' => NumberUtils::maximumDecimalPrecision($channel->getParam3() / 100, 2),
        ];
    }

    public function setParamsFromConfig(IODeviceChannel $channel, array $config) {
        if (array_key_exists('humidityAdjustment', $config)) {
            $channel->setParam3(intval($this->getValueInRange($config['humidityAdjustment'], -10, 10) * 100));
        }
    }

    public function supports(IODeviceChannel $channel): bool {
        return in_array($channel->getFunction()->getId(), [
            ChannelFunction::HUMIDITY,
            ChannelFunction::HUMIDITYANDTEMPERATURE,
        ]);
    }
}
