<?php

namespace SuplaBundle\Model\ChannelParamsTranslator;

use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelType;
use SuplaBundle\Utils\NumberUtils;

class ElectricityMeterParamsTranslator implements ChannelParamTranslator {
    use FixedRangeParamsTranslator;

    public function getConfigFromParams(IODeviceChannel $channel): array {
        return [
            'pricePerUnit' => NumberUtils::maximumDecimalPrecision($channel->getParam2() / 10000, 4),
            'currency' => $channel->getTextParam1(),
        ];
    }

    public function setParamsFromConfig(IODeviceChannel $channel, array $config) {
        if (isset($config['pricePerUnit'])) {
            $channel->setParam2(intval($this->getValueInRange($config['pricePerUnit'], 0, 1000) * 10000));
        }
        if (isset($config['currency'])) {
            $currency = $config['currency'];
            if (!$currency || preg_match('/^[A-Z]{3}$/', $currency)) {
                $channel->setTextParam1($currency);
            }
        }
    }

    public function supports(IODeviceChannel $channel): bool {
        return $channel->getType()->getId() == ChannelType::ELECTRICITYMETER && in_array($channel->getFunction()->getId(), [
                ChannelFunction::ELECTRICITYMETER,
            ]);
    }
}
