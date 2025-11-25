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
use Symfony\Contracts\Translation\TranslatorInterface;

class OpenWeatherVirtualChannelConfigurator implements VirtualChannelConfigurator {
    private const CONFIGS = [
        'temp' => [
            'type' => ChannelType::THERMOMETER,
            'function' => ChannelFunction::THERMOMETER,
            'properties' => ['hiddenConfigFields' => ['temperatureAdjustment']],
        ],
        'feelsLike' => [
            'type' => ChannelType::THERMOMETER,
            'function' => ChannelFunction::THERMOMETER,
            'properties' => ['hiddenConfigFields' => ['temperatureAdjustment']],
        ],
        'pressure' => ['type' => ChannelType::PRESSURESENSOR, 'function' => ChannelFunction::PRESSURESENSOR],
        'humidity' => [
            'type' => ChannelType::HUMIDITYSENSOR,
            'function' => ChannelFunction::HUMIDITY,
            'properties' => ['hiddenConfigFields' => ['humidityAdjustment']],
        ],
        'visibility' => ['type' => ChannelType::DISTANCESENSOR, 'function' => ChannelFunction::DISTANCESENSOR],
        'windSpeed' => ['type' => ChannelType::WINDSENSOR, 'function' => ChannelFunction::WINDSENSOR],
        'windGust' => ['type' => ChannelType::WINDSENSOR, 'function' => ChannelFunction::WINDSENSOR],
        'clouds' => [
            'type' => ChannelType::GENERAL_PURPOSE_MEASUREMENT,
            'function' => ChannelFunction::GENERAL_PURPOSE_MEASUREMENT,
            'altIcon' => 21,
            'userConfig' => ['valueMultiplier' => 1, 'valuePrecision' => 0, 'unitAfterValue' => '%'],
            'properties' => ['defaultValuePrecision' => 0, 'defaultUnitAfterValue' => '%', 'hiddenConfigFields' => [
                'keepHistory', 'chartType', 'refreshIntervalMs', 'valueMultiplier', 'valueDivider', 'valueAdded',
                'unitAfterValue', 'unitBeforeValue', 'valuePrecision',
            ]],
        ],
        'rainMmh' => ['type' => ChannelType::RAINSENSOR, 'function' => ChannelFunction::RAINSENSOR],
        'snowMmh' => ['type' => ChannelType::RAINSENSOR, 'function' => ChannelFunction::RAINSENSOR],
        'airCo' => [
            'type' => ChannelType::GENERAL_PURPOSE_MEASUREMENT,
            'function' => ChannelFunction::GENERAL_PURPOSE_MEASUREMENT,
            'altIcon' => 43,
            'userConfig' => ['valueMultiplier' => 1, 'valuePrecision' => 2, 'unitAfterValue' => 'µg/m³'],
            'properties' => ['defaultValuePrecision' => 2, 'defaultUnitAfterValue' => 'µg/m³', 'hiddenConfigFields' => [
                'keepHistory', 'chartType', 'refreshIntervalMs', 'valueMultiplier', 'valueDivider', 'valueAdded',
                'unitAfterValue', 'unitBeforeValue', 'valuePrecision',
            ]],
        ],
        'airNo' => [
            'type' => ChannelType::GENERAL_PURPOSE_MEASUREMENT,
            'function' => ChannelFunction::GENERAL_PURPOSE_MEASUREMENT,
            'altIcon' => 34,
            'userConfig' => ['valueMultiplier' => 1, 'valuePrecision' => 2, 'unitAfterValue' => 'µg/m³'],
            'properties' => ['defaultValuePrecision' => 2, 'defaultUnitAfterValue' => 'µg/m³', 'hiddenConfigFields' => [
                'keepHistory', 'chartType', 'refreshIntervalMs', 'valueMultiplier', 'valueDivider', 'valueAdded',
                'unitAfterValue', 'unitBeforeValue', 'valuePrecision',
            ]],
        ],
        'airNo2' => [
            'type' => ChannelType::GENERAL_PURPOSE_MEASUREMENT,
            'function' => ChannelFunction::GENERAL_PURPOSE_MEASUREMENT,
            'altIcon' => 37,
            'userConfig' => ['valueMultiplier' => 1, 'valuePrecision' => 2, 'unitAfterValue' => 'µg/m³'],
            'properties' => ['defaultValuePrecision' => 2, 'defaultUnitAfterValue' => 'µg/m³', 'hiddenConfigFields' => [
                'keepHistory', 'chartType', 'refreshIntervalMs', 'valueMultiplier', 'valueDivider', 'valueAdded',
                'unitAfterValue', 'unitBeforeValue', 'valuePrecision',
            ]],
        ],
        'airO3' => [
            'type' => ChannelType::GENERAL_PURPOSE_MEASUREMENT,
            'function' => ChannelFunction::GENERAL_PURPOSE_MEASUREMENT,
            'altIcon' => 40,
            'userConfig' => ['valueMultiplier' => 1, 'valuePrecision' => 2, 'unitAfterValue' => 'µg/m³'],
            'properties' => ['defaultValuePrecision' => 2, 'defaultUnitAfterValue' => 'µg/m³', 'hiddenConfigFields' => [
                'keepHistory', 'chartType', 'refreshIntervalMs', 'valueMultiplier', 'valueDivider', 'valueAdded',
                'unitAfterValue', 'unitBeforeValue', 'valuePrecision',
            ]],
        ],
        'airPm10' => [
            'type' => ChannelType::GENERAL_PURPOSE_MEASUREMENT,
            'function' => ChannelFunction::GENERAL_PURPOSE_MEASUREMENT,
            'altIcon' => 18,
            'userConfig' => ['valueMultiplier' => 1, 'valuePrecision' => 2, 'unitAfterValue' => 'µg/m³'],
            'properties' => ['defaultValuePrecision' => 2, 'defaultUnitAfterValue' => 'µg/m³', 'hiddenConfigFields' => [
                'keepHistory', 'chartType', 'refreshIntervalMs', 'valueMultiplier', 'valueDivider', 'valueAdded',
                'unitAfterValue', 'unitBeforeValue', 'valuePrecision',
            ]],
        ],
        'airPm25' => [
            'type' => ChannelType::GENERAL_PURPOSE_MEASUREMENT,
            'function' => ChannelFunction::GENERAL_PURPOSE_MEASUREMENT,
            'altIcon' => 17,
            'userConfig' => ['valueMultiplier' => 1, 'valuePrecision' => 2, 'unitAfterValue' => 'µg/m³'],
            'properties' => ['defaultValuePrecision' => 2, 'defaultUnitAfterValue' => 'µg/m³', 'hiddenConfigFields' => [
                'keepHistory', 'chartType', 'refreshIntervalMs', 'valueMultiplier', 'valueDivider', 'valueAdded',
                'unitAfterValue', 'unitBeforeValue', 'valuePrecision',
            ]],
        ],
        'tempHumidity' => [
            'type' => ChannelType::HUMIDITYANDTEMPSENSOR,
            'function' => ChannelFunction::HUMIDITYANDTEMPERATURE,
            'properties' => ['hiddenConfigFields' => ['temperatureAdjustment', 'humidityAdjustment']],
        ],
    ];

    public function __construct(
        private readonly SuplaAutodiscover $ad,
        private readonly SubjectConfigTranslator $configTranslator,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function configureChannel(IODeviceChannel $channel, array $config): IODeviceChannel {
        Assertion::keyExists($config, 'weatherField');
        Assertion::string($config['weatherField']);
        Assertion::keyExists(self::CONFIGS, $config['weatherField']);
        Assertion::keyExists($config, 'cityId');
        $openWeatherCities = $this->ad->getOpenWeatherCities();
        $city = current(array_filter($openWeatherCities, fn($city) => $city['id'] === $config['cityId']));
        Assertion::notNull($city);
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
        $locale = $channel->getUser()->getLocale();
        $attributeLabel = $this->translator->trans('openWeatherAttribute_field_' . $config['weatherField'], [], null, $locale);
        $channel->setCaption(sprintf('%s - %s', $city['name'], $attributeLabel));
        return $channel;
    }

    public function supports(VirtualChannelType $type): bool {
        return $type->getValue() === VirtualChannelType::OPEN_WEATHER;
    }

    public static function fieldNameToFunction(string $fieldName): ChannelFunction {
        return new ChannelFunction(self::CONFIGS[$fieldName]['function']);
    }
}
