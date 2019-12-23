<?php

namespace SuplaBundle\Model\ChannelParamsUpdater\ChannelParamsConfig;

use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelType;
use SuplaBundle\Utils\NumberUtils;

class ElectricityMeterParamsTranslator implements ChannelParamTranslator {
    public function getConfigFromParams(IODeviceChannel $channel): array {
        return [
            'pricePerUnit' => NumberUtils::maximumDecimalPrecision($channel->getParam2() / 10000, 4),
            'currency' => $channel->getTextParam1(),
        ];
    }

    public function setParamsFromConfig(IODeviceChannel $channel, array $config) {
        if (isset($config['pricePerUnit'])) {
            $channel->setParam2(intval($config['pricePerUnit'] * 10000));
        }
        $channel->setParam3($config['impulsesPerUnit'] ?? $channel->getParam3());
        $channel->setTextParam1($config['currency'] ?? $channel->getTextParam1());
    }

    public function supports(IODeviceChannel $channel): bool {
        return $channel->getType()->getId() == ChannelType::ELECTRICITYMETER && in_array($channel->getFunction()->getId(), [
                ChannelFunction::ELECTRICITYMETER,
            ]);
    }
}
