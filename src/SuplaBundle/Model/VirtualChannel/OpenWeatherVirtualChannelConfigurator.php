<?php

namespace SuplaBundle\Model\VirtualChannel;

use Assert\Assertion;
use SuplaBundle\Entity\EntityUtils;
use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\VirtualChannelType;
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
        'clouds' => ['function' => ChannelFunction::GENERAL_PURPOSE_MEASUREMENT],
        'rainMmh' => ['function' => ChannelFunction::RAINSENSOR],
        'snowMmh' => ['function' => ChannelFunction::RAINSENSOR],
        'airCo' => ['function' => ChannelFunction::GENERAL_PURPOSE_MEASUREMENT],
        'airNo' => ['function' => ChannelFunction::GENERAL_PURPOSE_MEASUREMENT],
        'airNo2' => ['function' => ChannelFunction::GENERAL_PURPOSE_MEASUREMENT],
        'airO3' => ['function' => ChannelFunction::GENERAL_PURPOSE_MEASUREMENT],
        'airPm10' => ['function' => ChannelFunction::GENERAL_PURPOSE_MEASUREMENT],
        'airPm25' => ['function' => ChannelFunction::GENERAL_PURPOSE_MEASUREMENT],
        'tempHumidity' => ['function' => ChannelFunction::HUMIDITYANDTEMPERATURE],
    ];

    public function __construct(private SuplaAutodiscover $ad) {
    }

    public function configureChannel(IODeviceChannel $channel, array $config): IODeviceChannel {
        Assertion::keyExists($config, 'weatherField');
        Assertion::keyExists(self::CONFIGS, $config['weatherField']);
        Assertion::keyExists($config, 'cityId');
        Assertion::inArray($config['cityId'], array_column($this->ad->getOpenWeatherCities(), 'id'));
        EntityUtils::setField($channel, 'properties', json_encode([
            'virtualChannelConfig' => [
                'type' => VirtualChannelType::OPEN_WEATHER,
                'cityId' => $config['cityId'],
                'weatherField' => $config['weatherField'],
            ],
        ]));
        EntityUtils::setField($channel, 'function', self::CONFIGS[$config['weatherField']]['function']);
        return $channel;
    }

    public function supports(VirtualChannelType $type): bool {
        return $type->getValue() === VirtualChannelType::OPEN_WEATHER;
    }
}
