<?php

namespace SuplaBundle\Model\UserConfigTranslator;

use Assert\Assertion;
use SuplaBundle\Entity\HasUserConfig;
use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelType;

class ValveConfigTranslator extends UserConfigTranslator {
    use ChannelNoToIdTranslator;

    public function getConfig(HasUserConfig $subject): array {
        return [
            'sensorChannelIds' => array_values(array_filter(array_map(
                fn(int $id) => $this->channelNoToChannel($subject, $id)?->getId(),
                $subject->getUserConfigValue('sensorChannelNumbers', [])
            ))),
        ];
    }

    public function setConfig(HasUserConfig $subject, array $config) {
        if (array_key_exists('sensorChannelIds', $config)) {
            Assertion::isArray($config['sensorChannelIds'], null, 'sensorChannelIds');
            Assertion::allInteger($config['sensorChannelIds'], null, 'sensorChannelIds');
            $sensors = array_map(fn(int $id) => $this->channelIdToChannelFromDevice($subject, $id), $config['sensorChannelIds']);
            Assertion::allEq(
                array_map(fn(IODeviceChannel $channel) => $channel->getType()->getId(), $sensors),
                ChannelType::SENSORNO,
                'Only binary sensors can be chosen for valve sensors.',
                'sensorChannelIds'
            );
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
