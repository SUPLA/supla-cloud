<?php

namespace SuplaBundle\Model\VirtualChannel;

use Assert\Assertion;
use SuplaBundle\Entity\EntityUtils;
use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\VirtualChannelType;
use SuplaBundle\Model\UserConfigTranslator\SubjectConfigTranslator;

class EnergyPriceForecastVirtualChannelConfigurator implements VirtualChannelConfigurator {
    private const CONFIGS = [
        'rde' => [
            'function' => ChannelFunction::GENERAL_PURPOSE_MEASUREMENT,
            'userConfig' => ['valueMultiplier' => 1, 'precision' => 0, 'unitAfterValue' => 'PLN'],
            'properties' => ['defaultValuePrecision' => 0, 'defaultUnitAfterValue' => 'PLN', 'hiddenConfigFields' => [
                'keepHistory', 'chartType', 'refreshIntervalMs',
            ]],
        ],
        'fixing1' => [
            'function' => ChannelFunction::GENERAL_PURPOSE_MEASUREMENT,
            'userConfig' => ['valueMultiplier' => 1, 'precision' => 0, 'unitAfterValue' => 'PLN'],
            'properties' => ['defaultValuePrecision' => 0, 'defaultUnitAfterValue' => 'PLN', 'hiddenConfigFields' => [
                'keepHistory', 'chartType', 'refreshIntervalMs',
            ]],
        ],
        'fixing2' => [
            'function' => ChannelFunction::GENERAL_PURPOSE_MEASUREMENT,
            'userConfig' => ['valueMultiplier' => 1, 'precision' => 0, 'unitAfterValue' => 'PLN'],
            'properties' => ['defaultValuePrecision' => 0, 'defaultUnitAfterValue' => 'PLN', 'hiddenConfigFields' => [
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
        EntityUtils::setField($channel, 'energyField', json_encode(array_merge([
            'virtualChannelConfig' => [
                'type' => VirtualChannelType::ENERGY_PRICE_FORECAST,
                'energyField' => $config['energyField'],
            ],
        ], $fieldConfig['properties'] ?? [])));
        EntityUtils::setField($channel, 'function', $fieldConfig['function']);
        $this->configTranslator->setConfig($channel, $fieldConfig['userConfig'] ?? []);
        return $channel;
    }

    public function supports(VirtualChannelType $type): bool {
        return $type->getValue() === VirtualChannelType::ENERGY_PRICE_FORECAST;
    }
}
