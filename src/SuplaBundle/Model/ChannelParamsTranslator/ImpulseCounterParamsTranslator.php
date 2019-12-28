<?php

namespace SuplaBundle\Model\ChannelParamsTranslator;

use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelType;
use SuplaBundle\Utils\NumberUtils;

class ImpulseCounterParamsTranslator implements ChannelParamTranslator {
    use FixedRangeParamsTranslator;

    public function getConfigFromParams(IODeviceChannel $channel): array {
        return [
            'pricePerUnit' => NumberUtils::maximumDecimalPrecision($channel->getParam2() / 10000, 4),
            'impulsesPerUnit' => $channel->getParam3(),
            'currency' => $channel->getTextParam1() ?: null,
            'unit' => $channel->getTextParam2() ?: null,
            'initialValue' => $channel->getParam1(),
        ];
    }

    public function setParamsFromConfig(IODeviceChannel $channel, array $config) {
        if (array_key_exists('initialValue', $config)) {
            $channel->setParam1($this->getValueInRange($config['initialValue'], 0, 1000000));
        }
        if (array_key_exists('pricePerUnit', $config)) {
            $channel->setParam2($this->getValueInRange($config['pricePerUnit'], 0, 1000) * 10000);
        }
        if (array_key_exists('impulsesPerUnit', $config)) {
            $channel->setParam3($this->getValueInRange($config['impulsesPerUnit'], 0, 1000000));
        }
        if (array_key_exists('currency', $config)) {
            $currency = $config['currency'];
            if (!$currency || preg_match('/^[A-Z]{3}$/', $currency)) {
                $channel->setTextParam1($currency);
            }
        }
        if (array_key_exists('unit', $config)) {
            if (mb_strlen($config['unit'] ?? '', 'UTF-8') <= 4) {
                $channel->setTextParam2($config['unit']);
            }
        }
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
