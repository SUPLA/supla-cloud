<?php

namespace SuplaBundle\Model\ChannelParamsUpdater\ChannelParamsConfig;

use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelType;

class CustomUnitParamTranslator implements ChannelParamTranslator {
    public function getConfigFromParams(IODeviceChannel $channel): array {
        return [
            'customUnit' => $channel->getTextParam2(),
        ];
    }

    public function setParamsFromConfig(array $config, IODeviceChannel $channel) {
        $channel->setTextParam2($config['customUnit'] ?? $channel->getTextParam2());
    }

    public function supports(IODeviceChannel $channel): bool {
        return $channel->getFunction()->getId() == ChannelFunction::GASMETER
            || $channel->getFunction()->getId() == ChannelFunction::WATERMETER
            || ($channel->getFunction()->getId() == ChannelFunction::ELECTRICITYMETER
                && $channel->getType()->getId() == ChannelType::IMPULSECOUNTER);
    }
}
