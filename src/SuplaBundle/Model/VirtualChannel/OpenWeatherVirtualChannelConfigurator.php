<?php

namespace SuplaBundle\Model\VirtualChannel;

use Assert\Assertion;
use SuplaBundle\Entity\EntityUtils;
use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\VirtualChannelType;
use SuplaBundle\Model\UserConfigTranslator\SubjectConfigTranslator;
use SuplaBundle\Supla\SuplaAutodiscover;

class OpenWeatherVirtualChannelConfigurator implements VirtualChannelConfigurator {
    private const CONFIGS = [
        'temp' => ['function' => ChannelFunction::THERMOMETER],
        'feelsLike' => ['function' => ChannelFunction::THERMOMETER],
        'pressure' => ['function' => ChannelFunction::PRESSURESENSOR],
        'humidity' => ['function' => ChannelFunction::HUMIDITY],
        'visibility' => ['function' => ChannelFunction::DISTANCESENSOR],
        'windSpeed' => ['function' => ChannelFunction::WINDSENSOR],
        'windGust' => ['function' => ChannelFunction::WINDSENSOR],
        'clouds' => [
            'function' => ChannelFunction::GENERAL_PURPOSE_MEASUREMENT,
            'userConfig' => ['valueMultiplier' => 1, 'valuePrecision' => 0, 'unitAfterValue' => '%'],
            'properties' => ['defaultValuePrecision' => 0, 'defaultUnitAfterValue' => '%', 'hiddenConfigFields' => [
                'keepHistory', 'chartType', 'refreshIntervalMs',
            ]],
        ],
        'rainMmh' => ['function' => ChannelFunction::RAINSENSOR],
        'snowMmh' => ['function' => ChannelFunction::RAINSENSOR],
        'airCo' => [
            'function' => ChannelFunction::GENERAL_PURPOSE_MEASUREMENT,
            'userConfig' => ['valueMultiplier' => 1, 'valuePrecision' => 0, 'unitAfterValue' => 'µg/m³'],
            'properties' => ['defaultValuePrecision' => 0, 'defaultUnitAfterValue' => 'µg/m³', 'hiddenConfigFields' => [
                'keepHistory', 'chartType', 'refreshIntervalMs',
            ]],
        ],
        'airNo' => [
            'function' => ChannelFunction::GENERAL_PURPOSE_MEASUREMENT,
            'userConfig' => ['valueMultiplier' => 1, 'valuePrecision' => 0, 'unitAfterValue' => 'µg/m³'],
            'properties' => ['defaultValuePrecision' => 0, 'defaultUnitAfterValue' => 'µg/m³', 'hiddenConfigFields' => [
                'keepHistory', 'chartType', 'refreshIntervalMs',
            ]],
        ],
        'airNo2' => [
            'function' => ChannelFunction::GENERAL_PURPOSE_MEASUREMENT,
            'userConfig' => ['valueMultiplier' => 1, 'valuePrecision' => 0, 'unitAfterValue' => 'µg/m³'],
            'properties' => ['defaultValuePrecision' => 0, 'defaultUnitAfterValue' => 'µg/m³', 'hiddenConfigFields' => [
                'keepHistory', 'chartType', 'refreshIntervalMs',
            ]],
        ],
        'airO3' => [
            'function' => ChannelFunction::GENERAL_PURPOSE_MEASUREMENT,
            'userConfig' => ['valueMultiplier' => 1, 'valuePrecision' => 0, 'unitAfterValue' => 'µg/m³'],
            'properties' => ['defaultValuePrecision' => 0, 'defaultUnitAfterValue' => 'µg/m³', 'hiddenConfigFields' => [
                'keepHistory', 'chartType', 'refreshIntervalMs',
            ]],
        ],
        'airPm10' => [
            'function' => ChannelFunction::GENERAL_PURPOSE_MEASUREMENT,
            'userConfig' => ['valueMultiplier' => 1, 'valuePrecision' => 0, 'unitAfterValue' => 'µg/m³'],
            'properties' => ['defaultValuePrecision' => 0, 'defaultUnitAfterValue' => 'µg/m³', 'hiddenConfigFields' => [
                'keepHistory', 'chartType', 'refreshIntervalMs',
            ]],
        ],
        'airPm25' => [
            'function' => ChannelFunction::GENERAL_PURPOSE_MEASUREMENT,
            'userConfig' => ['valueMultiplier' => 1, 'valuePrecision' => 0, 'unitAfterValue' => 'µg/m³'],
            'properties' => ['defaultValuePrecision' => 0, 'defaultUnitAfterValue' => 'µg/m³', 'hiddenConfigFields' => [
                'keepHistory', 'chartType', 'refreshIntervalMs',
            ]],
        ],
        'tempHumidity' => ['function' => ChannelFunction::HUMIDITYANDTEMPERATURE],
    ];

    public function __construct(private readonly SuplaAutodiscover $ad, private readonly SubjectConfigTranslator $configTranslator) {
    }

    public function configureChannel(IODeviceChannel $channel, array $config): IODeviceChannel {
        Assertion::keyExists($config, 'weatherField');
        Assertion::string($config['weatherField']);
        Assertion::keyExists(self::CONFIGS, $config['weatherField']);
        Assertion::keyExists($config, 'cityId');
        Assertion::inArray($config['cityId'], array_column($this->ad->getOpenWeatherCities(), 'id'));
        $fieldConfig = self::CONFIGS[$config['weatherField']];
        EntityUtils::setField($channel, 'properties', json_encode(array_merge([
            'virtualChannelConfig' => [
                'type' => VirtualChannelType::OPEN_WEATHER,
                'cityId' => $config['cityId'],
                'weatherField' => $config['weatherField'],
            ],
        ], $fieldConfig['properties'] ?? [])));
        EntityUtils::setField($channel, 'function', $fieldConfig['function']);
        $this->configTranslator->setConfig($channel, $fieldConfig['userConfig'] ?? []);
        return $channel;
    }

    public function supports(VirtualChannelType $type): bool {
        return $type->getValue() === VirtualChannelType::OPEN_WEATHER;
    }

    public static function fieldNameToFunction(string $fieldName): ChannelFunction {
        return new ChannelFunction(self::CONFIGS[$fieldName]['function']);
    }
}
