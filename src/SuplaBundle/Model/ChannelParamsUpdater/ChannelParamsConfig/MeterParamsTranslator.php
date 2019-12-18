<?php

namespace SuplaBundle\Model\ChannelParamsUpdater\ChannelParamsConfig;

use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;

class MeterParamsTranslator implements ChannelParamTranslator {
    public function getConfigFromParams(IODeviceChannel $channel): array {
        return [
            'pricePerUnit' => $channel->getParam2(),
            'impulsesPerUnit' => $channel->getParam3(),
            'currency' => $channel->getTextParam1(),
        ];
    }

    public function setParamsFromConfig(array $config, IODeviceChannel $channel) {
        $channel->setParam2($config['pricePerUnit'] ?? $channel->getParam2());
        $channel->setParam3($config['impulsesPerUnit'] ?? $channel->getParam3());
        $channel->setTextParam1($config['currency'] ?? $channel->getTextParam1());
    }

    public function supports(IODeviceChannel $channel): bool {
        return in_array($channel->getFunction()->getId(), [
            ChannelFunction::ELECTRICITYMETER,
            ChannelFunction::GASMETER,
            ChannelFunction::WATERMETER,
        ]);
    }
}
