<?php

namespace SuplaBundle\Model\ChannelParamsUpdater\ChannelParamsConfig;

use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelType;
use SuplaBundle\Utils\NumberUtils;

class ImpulseCounterParamsTranslator implements ChannelParamTranslator {
    public function getConfigFromParams(IODeviceChannel $channel): array {
        return [
            'pricePerUnit' => NumberUtils::maximumDecimalPrecision($channel->getParam2() / 10000, 4),
            'impulsesPerUnit' => $channel->getParam3(),
            'currency' => $channel->getTextParam1(),
            'customUnit' => $channel->getTextParam2(),
            'initialValue' => $channel->getParam1(),
        ];
    }

    public function setParamsFromConfig(IODeviceChannel $channel, array $config) {
        $channel->setParam1($config['initialValue'] ?? $channel->getParam1());
        if (isset($config['pricePerUnit'])) {
            $channel->setParam2(intval($config['pricePerUnit'] * 10000));
        }
        $channel->setParam3($config['impulsesPerUnit'] ?? $channel->getParam3());
        $channel->setTextParam1($config['currency'] ?? $channel->getTextParam1());
        $channel->setTextParam2($config['customUnit'] ?? $channel->getTextParam2());
    }

    public function supports(IODeviceChannel $channel): bool {
        return $channel->getType()->getId() == ChannelType::IMPULSECOUNTER &&
            in_array($channel->getFunction()->getId(), [
                ChannelFunction::ELECTRICITYMETER,
                ChannelFunction::GASMETER,
                ChannelFunction::WATERMETER,
            ]);
    }
}
