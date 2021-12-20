<?php

namespace SuplaBundle\Model\ChannelParamsTranslator;

use OpenApi\Annotations as OA;
use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelFunctionBitsFlags;
use SuplaBundle\Utils\NumberUtils;

/**
 * @OA\Schema(schema="ChannelConfigStaircaseTimer", description="Config for `STAIRCASETIMER`.",
 *   @OA\Property(property="timeSettingAvailable", type="boolean", readOnly=true),
 *   @OA\Property(property="relayTimeS", type="integer", minimum=0, maximum=7200),
 *   @OA\Property(property="relatedChannelId", type="integer"),
 * )
 */
class RelayTimeSChannelParamTranslator implements ChannelParamTranslator {
    use FixedRangeParamsTranslator;

    public function getConfigFromParams(IODeviceChannel $channel): array {
        return [
            'relayTimeS' => NumberUtils::maximumDecimalPrecision($channel->getParam1() / 10, 1),
            'timeSettingAvailable' => !ChannelFunctionBitsFlags::TIME_SETTING_NOT_AVAILABLE()->isSupported($channel->getFlags()),
        ];
    }

    public function setParamsFromConfig(IODeviceChannel $channel, array $config) {
        if (array_key_exists('relayTimeS', $config)) {
            $channel->setParam1(intval($this->getValueInRange($config['relayTimeS'], 0, 7200) * 10));
        }
    }

    public function supports(IODeviceChannel $channel): bool {
        return in_array($channel->getFunction()->getId(), [
            ChannelFunction::STAIRCASETIMER,
        ]);
    }
}
