<?php

namespace App\Model\VirtualChannel;

use App\Entity\EntityUtils;
use App\Entity\Main\IODeviceChannel;
use App\Enums\ChannelFunction;
use App\Enums\ChannelType;
use App\Enums\VirtualChannelType;
use App\Model\UserConfigTranslator\SubjectConfigTranslator;
use Assert\Assertion;
use Symfony\Contracts\Translation\TranslatorInterface;

class EnergyPriceForecastVirtualChannelConfigurator implements VirtualChannelConfigurator {
    private const CONFIGS = [
        'rce' => [
            'type' => ChannelType::GENERAL_PURPOSE_MEASUREMENT,
            'function' => ChannelFunction::GENERAL_PURPOSE_MEASUREMENT,
            'altIcon' => 45,
            'userConfig' => ['valueMultiplier' => 1, 'valuePrecision' => 2, 'unitAfterValue' => 'zł/MWh'],
            'properties' => ['defaultValuePrecision' => 2, 'defaultUnitAfterValue' => 'zł/MWh', 'hiddenConfigFields' => [
                'keepHistory', 'chartType', 'refreshIntervalMs', 'valueMultiplier', 'valueDivider', 'valueAdded',
                'unitAfterValue', 'unitBeforeValue', 'valuePrecision',
            ]],
        ],
        'pdgsz' => [
            'type' => ChannelType::GENERAL_PURPOSE_MEASUREMENT,
            'function' => ChannelFunction::GENERAL_PURPOSE_MEASUREMENT,
            'altIcon' => 45,
            'userConfig' => ['valueMultiplier' => 1, 'valuePrecision' => 0, 'unitAfterValue' => ''],
            'properties' => ['defaultValuePrecision' => 0, 'defaultUnitAfterValue' => '', 'hiddenConfigFields' => [
                'keepHistory', 'chartType', 'refreshIntervalMs', 'valueMultiplier', 'valueDivider', 'valueAdded',
                'unitAfterValue', 'unitBeforeValue', 'valuePrecision',
            ]],
        ],
        'fixing1' => [
            'type' => ChannelType::GENERAL_PURPOSE_MEASUREMENT,
            'function' => ChannelFunction::GENERAL_PURPOSE_MEASUREMENT,
            'altIcon' => 45,
            'userConfig' => ['valueMultiplier' => 1, 'valuePrecision' => 2, 'unitAfterValue' => 'zł/MWh'],
            'properties' => ['defaultValuePrecision' => 2, 'defaultUnitAfterValue' => 'zł/MWh', 'hiddenConfigFields' => [
                'keepHistory', 'chartType', 'refreshIntervalMs', 'valueMultiplier', 'valueDivider', 'valueAdded',
                'unitAfterValue', 'unitBeforeValue', 'valuePrecision',
            ]],
        ],
        'fixing2' => [
            'type' => ChannelType::GENERAL_PURPOSE_MEASUREMENT,
            'function' => ChannelFunction::GENERAL_PURPOSE_MEASUREMENT,
            'altIcon' => 45,
            'userConfig' => ['valueMultiplier' => 1, 'valuePrecision' => 2, 'unitAfterValue' => 'zł/MWh'],
            'properties' => ['defaultValuePrecision' => 2, 'defaultUnitAfterValue' => 'zł/MWh', 'hiddenConfigFields' => [
                'keepHistory', 'chartType', 'refreshIntervalMs', 'valueMultiplier', 'valueDivider', 'valueAdded',
                'unitAfterValue', 'unitBeforeValue', 'valuePrecision',
            ]],
        ],
    ];

    public function __construct(
        private readonly SubjectConfigTranslator $configTranslator,
        private readonly TranslatorInterface $translator,
    ) {
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
        $locale = $channel->getUser()->getLocale();
        $channel->setCaption($this->translator->trans('energyPriceForecast_field_' . $config['energyField'], [], null, $locale));
        return $channel;
    }

    public function supports(VirtualChannelType $type): bool {
        return $type->getValue() === VirtualChannelType::ENERGY_PRICE_FORECAST;
    }
}
