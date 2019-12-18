<?php

namespace SuplaBundle\Model\ChannelParamsUpdater\ChannelParamsConfig;

use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;

class InvertedLogicParamTranslator implements ChannelParamTranslator {
    public function getConfigFromParams(IODeviceChannel $channel): array {
        return [
            'invertedLogic' => boolval($channel->getParam3()),
        ];
    }

    public function setParamsFromConfig(array $config, IODeviceChannel $channel) {
        $channel->setParam3(($config['invertedLogic'] ?? $channel->getParam3()) ? 1 : 0);
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
