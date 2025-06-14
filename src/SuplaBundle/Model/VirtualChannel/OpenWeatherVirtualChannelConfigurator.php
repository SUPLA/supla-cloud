<?php

namespace SuplaBundle\Model\VirtualChannel;

use Assert\Assertion;
use SuplaBundle\Entity\EntityUtils;
use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelType;
use SuplaBundle\Enums\VirtualChannelType;
use SuplaBundle\Model\UserConfigTranslator\SubjectConfigTranslator;
use SuplaBundle\Supla\SuplaAutodiscover;

class OpenWeatherVirtualChannelConfigurator implements VirtualChannelConfigurator {
    private const CONFIGS = [
        'temp' => ['type' => ChannelType::THERMOMETER, 'function' => ChannelFunction::THERMOMETER],
        'feelsLike' => ['type' => ChannelFunction::THERMOMETER, 'function' => ChannelFunction::THERMOMETER],
        'pressure' => ['type' => ChannelType::PRESSURESENSOR, 'function' => ChannelFunction::PRESSURESENSOR],
        'humidity' => ['type' => ChannelType::HUMIDITYSENSOR, 'function' => ChannelFunction::HUMIDITY],
        'visibility' => ['type' => ChannelType::DISTANCESENSOR, 'function' => ChannelFunction::DISTANCESENSOR],
        'windSpeed' => ['type' => ChannelType::WINDSENSOR, 'function' => ChannelFunction::WINDSENSOR],
        'windGust' => ['type' => ChannelType::WINDSENSOR, 'function' => ChannelFunction::WINDSENSOR],
        'clouds' => [
            'type' => ChannelType::GENERAL_PURPOSE_MEASUREMENT,
            'function' => ChannelFunction::GENERAL_PURPOSE_MEASUREMENT,
            'altIcon' => 21,
            'userConfig' => ['valueMultiplier' => 1, 'valuePrecision' => 0, 'unitAfterValue' => '%'],
            'properties' => ['defaultValuePrecision' => 0, 'defaultUnitAfterValue' => '%', 'hiddenConfigFields' => [
                'keepHistory', 'chartType', 'refreshIntervalMs',
            ]],
        ],
        'rainMmh' => ['type' => ChannelType::RAINSENSOR, 'function' => ChannelFunction::RAINSENSOR],
        'snowMmh' => ['type' => ChannelType::RAINSENSOR, 'function' => ChannelFunction::RAINSENSOR],
        'airCo' => [
            'type' => ChannelType::GENERAL_PURPOSE_MEASUREMENT,
            'function' => ChannelFunction::GENERAL_PURPOSE_MEASUREMENT,
            'altIcon' => 25,
            'userConfig' => ['valueMultiplier' => 1, 'valuePrecision' => 0, 'unitAfterValue' => 'µg/m³'],
            'properties' => ['defaultValuePrecision' => 0, 'defaultUnitAfterValue' => 'µg/m³', 'hiddenConfigFields' => [
                'keepHistory', 'chartType', 'refreshIntervalMs',
            ]],
        ],
        'airNo' => [
            'type' => ChannelType::GENERAL_PURPOSE_MEASUREMENT,
            'function' => ChannelFunction::GENERAL_PURPOSE_MEASUREMENT,
            'altIcon' => 20,
            'userConfig' => ['valueMultiplier' => 1, 'valuePrecision' => 0, 'unitAfterValue' => 'µg/m³'],
            'properties' => ['defaultValuePrecision' => 0, 'defaultUnitAfterValue' => 'µg/m³', 'hiddenConfigFields' => [
                'keepHistory', 'chartType', 'refreshIntervalMs',
            ]],
        ],
        'airNo2' => [
            'type' => ChannelType::GENERAL_PURPOSE_MEASUREMENT,
            'function' => ChannelFunction::GENERAL_PURPOSE_MEASUREMENT,
            'altIcon' => 20,
            'userConfig' => ['valueMultiplier' => 1, 'valuePrecision' => 0, 'unitAfterValue' => 'µg/m³'],
            'properties' => ['defaultValuePrecision' => 0, 'defaultUnitAfterValue' => 'µg/m³', 'hiddenConfigFields' => [
                'keepHistory', 'chartType', 'refreshIntervalMs',
            ]],
        ],
        'airO3' => [
            'type' => ChannelType::GENERAL_PURPOSE_MEASUREMENT,
            'function' => ChannelFunction::GENERAL_PURPOSE_MEASUREMENT,
            'altIcon' => 20,
            'userConfig' => ['valueMultiplier' => 1, 'valuePrecision' => 0, 'unitAfterValue' => 'µg/m³'],
            'properties' => ['defaultValuePrecision' => 0, 'defaultUnitAfterValue' => 'µg/m³', 'hiddenConfigFields' => [
                'keepHistory', 'chartType', 'refreshIntervalMs',
            ]],
        ],
        'airPm10' => [
            'type' => ChannelType::GENERAL_PURPOSE_MEASUREMENT,
            'function' => ChannelFunction::GENERAL_PURPOSE_MEASUREMENT,
            'altIcon' => 18,
            'userConfig' => ['valueMultiplier' => 1, 'valuePrecision' => 0, 'unitAfterValue' => 'µg/m³'],
            'properties' => ['defaultValuePrecision' => 0, 'defaultUnitAfterValue' => 'µg/m³', 'hiddenConfigFields' => [
                'keepHistory', 'chartType', 'refreshIntervalMs',
            ]],
        ],
        'airPm25' => [
            'type' => ChannelType::GENERAL_PURPOSE_MEASUREMENT,
            'function' => ChannelFunction::GENERAL_PURPOSE_MEASUREMENT,
            'altIcon' => 17,
            'userConfig' => ['valueMultiplier' => 1, 'valuePrecision' => 0, 'unitAfterValue' => 'µg/m³'],
            'properties' => ['defaultValuePrecision' => 0, 'defaultUnitAfterValue' => 'µg/m³', 'hiddenConfigFields' => [
                'keepHistory', 'chartType', 'refreshIntervalMs',
            ]],
        ],
        'tempHumidity' => ['type' => ChannelType::HUMIDITYANDTEMPSENSOR, 'function' => ChannelFunction::HUMIDITYANDTEMPERATURE],
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
        EntityUtils::setField($channel, 'type', $fieldConfig['type']);
        EntityUtils::setField($channel, 'function', $fieldConfig['function']);
        $channel->setAltIcon($fieldConfig['altIcon'] ?? 0);
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
