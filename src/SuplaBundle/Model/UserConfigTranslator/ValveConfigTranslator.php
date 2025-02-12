<?php

namespace SuplaBundle\Model\UserConfigTranslator;

use Assert\Assertion;
use SuplaBundle\Entity\HasUserConfig;
use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelFunctionBitsFlags;
use SuplaBundle\Enums\ChannelType;

class ValveConfigTranslator extends UserConfigTranslator {
    use ChannelNoToIdTranslator;

    public function getConfig(HasUserConfig $subject): array {
        $config = [];
        if ($subject instanceof IODeviceChannel) {
            if (ChannelFunctionBitsFlags::FLOOD_SENSORS_SUPPORTED()->isSupported($subject->getFlags())) {
                $config['floodSensorChannelIds'] = array_values(array_filter(array_map(
                    fn(int $id) => $this->channelNoToChannel($subject, $id)?->getId(),
                    $subject->getUserConfigValue('sensorChannelNumbers', [])
                )));
            }
        }
        return $config;
    }

    public function setConfig(HasUserConfig $subject, array $config) {
        if (array_key_exists('floodSensorChannelIds', $config) && $config['floodSensorChannelIds'] !== null) {
            Assertion::true(
                ChannelFunctionBitsFlags::FLOOD_SENSORS_SUPPORTED()->isSupported($subject->getFlags()),
                'Flood sesnors not supported in this channel.'
            );
            Assertion::isArray($config['floodSensorChannelIds'], null, 'sensorChannelIds');
            Assertion::allInteger($config['floodSensorChannelIds'], null, 'sensorChannelIds');
            $sensors = array_map(fn(int $id) => $this->channelIdToChannelFromDevice($subject, $id), $config['floodSensorChannelIds']);
            Assertion::allEq(
                array_map(fn(IODeviceChannel $channel) => $channel->getType()->getId(), $sensors),
                ChannelType::SENSORNO,
                'Only binary sensors can be chosen for valve sensors.',
                'floodSensorChannelIds'
            );
            Assertion::maxCount($sensors, 20, 'Valve supports up to 20 sensors.'); // i18n
            $subject->setUserConfigValue(
                'sensorChannelNumbers',
                array_map(fn(IODeviceChannel $channel) => $channel->getChannelNumber(), $sensors),
            );
        }
    }

    public function supports(HasUserConfig $subject): bool {
        return in_array($subject->getFunction()->getId(), [
            ChannelFunction::VALVEOPENCLOSE,
        ]);
    }
}
