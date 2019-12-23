<?php

namespace SuplaBundle\Model\ChannelParamsTranslator;

use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;

class InvertedLogicParamTranslator implements ChannelParamTranslator {
    use FixedRangeParamsTranslator;

    public function getConfigFromParams(IODeviceChannel $channel): array {
        return [
            'invertedLogic' => boolval($channel->getParam3()),
        ];
    }

    public function setParamsFromConfig(IODeviceChannel $channel, array $config) {
        if (isset($config['invertedLogic'])) {
            $channel->setParam3($this->getValueInRange($config['invertedLogic'], 0, 1) ? 1 : 0);
        }
    }

    public function supports(IODeviceChannel $channel): bool {
        return in_array($channel->getFunction()->getId(), [
            ChannelFunction::MAILSENSOR,
            ChannelFunction::NOLIQUIDSENSOR,
            ChannelFunction::OPENINGSENSOR_DOOR,
            ChannelFunction::OPENINGSENSOR_GARAGEDOOR,
            ChannelFunction::OPENINGSENSOR_GATE,
            ChannelFunction::OPENINGSENSOR_GATEWAY,
            ChannelFunction::OPENINGSENSOR_ROLLERSHUTTER,
            ChannelFunction::OPENINGSENSOR_WINDOW,
        ]);
    }
}
