<?php

namespace SuplaBundle\Model\ChannelParamsTranslator;

use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelFunctionBitsFlags;

class RelayTimeMsChannelParamTranslator implements ChannelParamTranslator {
    use FixedRangeParamsTranslator;

    private const TIMES = [
        ChannelFunction::CONTROLLINGTHEDOORLOCK => [500, 10000],
        ChannelFunction::CONTROLLINGTHEGARAGEDOOR => [500, 2000],
        ChannelFunction::CONTROLLINGTHEGATE => [500, 2000],
        ChannelFunction::CONTROLLINGTHEGATEWAYLOCK => [500, 10000],
    ];

    public function getConfigFromParams(IODeviceChannel $channel): array {
        return [
            'relayTimeMs' => $channel->getParam1() ?: 500,
            'timeSettingAvailable' => !ChannelFunctionBitsFlags::TIME_SETTING_NOT_AVAILABLE()->isSupported($channel->getFlags()),
        ];
    }

    public function setParamsFromConfig(IODeviceChannel $channel, array $config) {
        if (array_key_exists('relayTimeMs', $config)) {
            $times = self::TIMES[$channel->getFunction()->getId()] ?? [500, 10000];
            $channel->setParam1($this->getValueInRange($config['relayTimeMs'], $times[0], $times[1]));
        }
    }

    public function supports(IODeviceChannel $channel): bool {
        return in_array($channel->getFunction()->getId(), [
            ChannelFunction::CONTROLLINGTHEDOORLOCK,
            ChannelFunction::CONTROLLINGTHEGARAGEDOOR,
            ChannelFunction::CONTROLLINGTHEGATE,
            ChannelFunction::CONTROLLINGTHEGATEWAYLOCK,
        ]);
    }
}
