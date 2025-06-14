<?php

namespace SuplaBundle\Model\VirtualChannel;

use Assert\Assertion;
use SuplaBundle\Entity\EntityUtils;
use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelType;
use SuplaBundle\Enums\VirtualChannelType;
use SuplaBundle\Model\UserConfigTranslator\SubjectConfigTranslator;

class EnergyPriceForecastVirtualChannelConfigurator implements VirtualChannelConfigurator {
    private const CONFIGS = [
        'rce' => [
            'type' => ChannelType::GENERAL_PURPOSE_MEASUREMENT,
            'function' => ChannelFunction::GENERAL_PURPOSE_MEASUREMENT,
            'altIcon' => 28,
            'userConfig' => ['valueMultiplier' => 1, 'valuePrecision' => 2, 'unitAfterValue' => 'PLN'],
            'properties' => ['defaultValuePrecision' => 2, 'defaultUnitAfterValue' => 'PLN', 'hiddenConfigFields' => [
                'keepHistory', 'chartType', 'refreshIntervalMs',
            ]],
        ],
        'fixing1' => [
            'type' => ChannelType::GENERAL_PURPOSE_MEASUREMENT,
            'function' => ChannelFunction::GENERAL_PURPOSE_MEASUREMENT,
            'altIcon' => 28,
            'userConfig' => ['valueMultiplier' => 1, 'valuePrecision' => 2, 'unitAfterValue' => 'PLN'],
            'properties' => ['defaultValuePrecision' => 2, 'defaultUnitAfterValue' => 'PLN', 'hiddenConfigFields' => [
                'keepHistory', 'chartType', 'refreshIntervalMs',
            ]],
        ],
        'fixing2' => [
            'type' => ChannelType::GENERAL_PURPOSE_MEASUREMENT,
            'function' => ChannelFunction::GENERAL_PURPOSE_MEASUREMENT,
            'altIcon' => 28,
            'userConfig' => ['valueMultiplier' => 1, 'valuePrecision' => 2, 'unitAfterValue' => 'PLN'],
            'properties' => ['defaultValuePrecision' => 2, 'defaultUnitAfterValue' => 'PLN', 'hiddenConfigFields' => [
                'keepHistory', 'chartType', 'refreshIntervalMs',
            ]],
        ],
    ];

    public function __construct(private readonly SubjectConfigTranslator $configTranslator) {
    }

    public function configureChannel(IODeviceChannel $channel, array $config): IODeviceChannel {
        Assertion::keyExists($config, 'energyField');
        Assertion::string($config['energyField']);
        Assertion::keyExists(self::CONFIGS, $config['energyField']);
        $fieldConfig = self::CONFIGS[$config['energyField']];
        EntityUtils::setField($channel, 'properties', json_encode(array_merge([
            'virtualChannelConfig' => [
                'type' => VirtualChannelType::ENERGY_PRICE_FORECAST,
                'energyField' => $config['energyField'],
            ],
        ], $fieldConfig['properties'] ?? [])));
        EntityUtils::setField($channel, 'type', $fieldConfig['type']);
        EntityUtils::setField($channel, 'function', $fieldConfig['function']);
        $channel->setAltIcon($fieldConfig['altIcon'] ?? 0);
        $this->configTranslator->setConfig($channel, $fieldConfig['userConfig'] ?? []);
        return $channel;
    }

    public function supports(VirtualChannelType $type): bool {
        return $type->getValue() === VirtualChannelType::ENERGY_PRICE_FORECAST;
    }
}
