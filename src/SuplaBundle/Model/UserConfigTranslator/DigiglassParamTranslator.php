<?php

namespace SuplaBundle\Model\UserConfigTranslator;

use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;

class DigiglassParamTranslator implements ChannelParamTranslator {
    use FixedRangeParamsTranslator;

    const MINUTES_IN_DAY = 1440;
    const MAX_SECTIONS = 7;

    public function getConfigFromParams(IODeviceChannel $channel): array {
        return [
            'sectionsCount' => $channel->getParam1(),
            'regenerationTimeStart' => $channel->getParam2(),
        ];
    }

    public function setParamsFromConfig(IODeviceChannel $channel, array $config) {
        if (array_key_exists('sectionsCount', $config)) {
            $channel->setParam1(intval($this->getValueInRange($config['sectionsCount'], 1, self::MAX_SECTIONS)));
        }
        if (array_key_exists('regenerationTimeStart', $config)) {
            $channel->setParam2(intval($this->getValueInRange($config['regenerationTimeStart'], 0, self::MINUTES_IN_DAY - 1)));
        }
    }

    public function supports(IODeviceChannel $channel): bool {
        return in_array($channel->getFunction()->getId(), [
            ChannelFunction::DIGIGLASS_VERTICAL,
            ChannelFunction::DIGIGLASS_HORIZONTAL,
        ]);
    }
}
