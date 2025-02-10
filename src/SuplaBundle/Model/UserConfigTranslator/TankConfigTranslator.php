<?php

namespace SuplaBundle\Model\UserConfigTranslator;

use Assert\Assert;
use Assert\Assertion;
use SuplaBundle\Entity\HasUserConfig;
use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelType;

class TankConfigTranslator extends UserConfigTranslator {
    use ChannelNoToIdTranslator;

    public function getConfig(HasUserConfig $subject): array {
        $levelSensors = [];
        foreach ($subject->getUserConfigValue('sensors', []) as $channelNo => $lvlConfig) {
            $id = $this->channelNoToChannel($subject, $channelNo)?->getId();
            if ($id) {
                $levelSensors[] = array_merge($lvlConfig, ['id' => $id]);
            }
        }
        usort($levelSensors, fn($a, $b) => $a['fillLevel'] - $b['fillLevel']);
        return [
            'levelSensors' => $levelSensors,
            'levelSensorChannelIds' => array_column($levelSensors, 'id'),
            'warningAboveLevel' => $subject->getUserConfigValue('warningAboveLevel'),
            'alarmAboveLevel' => $subject->getUserConfigValue('alarmAboveLevel'),
            'warningBelowLevel' => $subject->getUserConfigValue('warningBelowLevel'),
            'alarmBelowLevel' => $subject->getUserConfigValue('alarmBelowLevel'),
            'muteAlarmSoundWithoutAdditionalAuth' => boolval($subject->getUserConfigValue('muteAlarmSoundWithoutAdditionalAuth')),
        ];
    }

    public function setConfig(HasUserConfig $subject, array $config) {
        if (array_key_exists('levelSensors', $config)) {
            Assertion::isArray($config['levelSensors'], null, 'levelSensors');
            Assert::thatAll($config['levelSensors'], null, 'levelSensors')
                ->isArray()->keyExists('id')->keyExists('fillLevel');
            $sensors = array_map(fn(array $sensor
            ) => $this->channelIdToChannelFromDevice($subject, $sensor['id']), $config['levelSensors']);
            Assertion::allEq(
                array_map(fn(IODeviceChannel $channel) => $channel->getType()->getId(), $sensors),
                ChannelType::SENSORNO,
                'Only binary sensors can be chosen for valve sensors.',
                'levelSensors'
            );
            $options = array_map(fn(array $sensor) => array_intersect_key($sensor, ['fillLevel' => '']), $config['levelSensors']);
            Assert::thatAll(array_column($options, 'fillLevel'), null, 'levelSensors.fillLevel')
                ->integer()->between(0, 100);
            Assertion::uniqueValues(array_column($options, 'fillLevel'), null, 'levelSensors.fillLevel');
            $subject->setUserConfigValue(
                'sensors',
                array_combine(
                    array_map(fn(IODeviceChannel $channel) => $channel->getChannelNumber(), $sensors),
                    $options
                )
            );
        }
        $availableFillLevels = array_column($subject->getUserConfigValue('sensors', []), 'fillLevel');
        foreach (['warningAboveLevel', 'alarmAboveLevel', 'warningBelowLevel', 'alarmBelowLevel'] as $fillLevel) {
            if (array_key_exists($fillLevel, $config)) {
                if (is_int($config[$fillLevel])) {
                    Assertion::inArray($config[$fillLevel], $availableFillLevels, null, $fillLevel);
                    $subject->setUserConfigValue($fillLevel, $config[$fillLevel]);
                } else {
                    $subject->setUserConfigValue($fillLevel, null);
                }
            }
            $level = $subject->getUserConfigValue($fillLevel);
            if (is_int($level) && !in_array($level, $availableFillLevels)) {
                $subject->setUserConfigValue($fillLevel, null);
            }
        }
        if (array_key_exists('muteAlarmSoundWithoutAdditionalAuth', $config)) {
            $subject->setUserConfigValue('muteAlarmSoundWithoutAdditionalAuth', boolval($config['muteAlarmSoundWithoutAdditionalAuth']));
        }
    }

    public function supports(HasUserConfig $subject): bool {
        return in_array($subject->getFunction()->getId(), [
            ChannelFunction::CONTAINER,
            ChannelFunction::SEPTIC_TANK,
        ]);
    }
}
