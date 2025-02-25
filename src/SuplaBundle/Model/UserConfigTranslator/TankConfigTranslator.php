<?php

namespace SuplaBundle\Model\UserConfigTranslator;

use Assert\Assert;
use Assert\Assertion;
use SuplaBundle\Entity\HasUserConfig;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelFunctionBitsFlags;
use SuplaBundle\Enums\ChannelType;
use SuplaBundle\Utils\ArrayUtils;

class TankConfigTranslator extends UserConfigTranslator {
    use ChannelNoToIdTranslator;

    public function getConfig(HasUserConfig $subject): array {
        $levelSensors = [];
        foreach ($subject->getUserConfigValue('sensors', []) as $lvlConfig) {
            $id = $this->channelNoToChannel($subject, $lvlConfig['channelNo'] ?? 0)?->getId();
            if ($id) {
                $levelSensors[] = array_merge(['channelId' => $id, 'fillLevel' => $lvlConfig['fillLevel']]);
            }
        }
        usort($levelSensors, fn($a, $b) => $a['fillLevel'] - $b['fillLevel']);
        return [
            'levelSensors' => $levelSensors,
            'levelSensorChannelIds' => array_column($levelSensors, 'channelId'),
            'warningAboveLevel' => $subject->getUserConfigValue('warningAboveLevel'),
            'alarmAboveLevel' => $subject->getUserConfigValue('alarmAboveLevel'),
            'warningBelowLevel' => $subject->getUserConfigValue('warningBelowLevel'),
            'alarmBelowLevel' => $subject->getUserConfigValue('alarmBelowLevel'),
            'muteAlarmSoundWithoutAdditionalAuth' => boolval($subject->getUserConfigValue('muteAlarmSoundWithoutAdditionalAuth')),
            'fillLevelReportingInFullRange' =>
                ChannelFunctionBitsFlags::TANK_FILL_LEVEL_REPORTING_IN_FULL_RANGE()->isOn($subject->getFlags()),
        ];
    }

    public function setConfig(HasUserConfig $subject, array $config) {
        if (array_key_exists('levelSensorChannelIds', $config)
            && !array_key_exists('levelSensors', $config)
            && $config['levelSensorChannelIds'] !== null) {
            // request from ChannelDependencies clearing
            Assertion::isArray($config['levelSensorChannelIds'], null, 'levelSensorChannelIds');
            $currentConfig = $this->getConfig($subject);
            $config['levelSensors'] = ArrayUtils::filter(
                $currentConfig['levelSensors'],
                fn($sensor) => in_array($sensor['channelId'], $config['levelSensorChannelIds'])
            );
        }
        if (array_key_exists('levelSensors', $config) && $config['levelSensors'] !== null) {
            Assertion::isArray($config['levelSensors'], null, 'levelSensors');
            Assert::thatAll($config['levelSensors'], null, 'levelSensors')
                ->isArray()->keyExists('channelId')->keyExists('fillLevel');

            $options = array_map(function (array $lvlConfig) use ($subject) {
                $sensor = $this->channelIdToChannelFromDevice($subject, $lvlConfig['channelId']);
                Assertion::eq(
                    $sensor->getType()->getId(),
                    ChannelType::SENSORNO,
                    'Only binary sensors can be chosen for valve sensors.',
                    'levelSensors'
                );
                return ['channelNo' => $sensor->getChannelNumber(), 'fillLevel' => $lvlConfig['fillLevel']];
            }, $config['levelSensors']);
            Assert::thatAll(array_column($options, 'fillLevel'), null, 'levelSensors.fillLevel')
                ->integer()->between(0, 100);
            Assertion::uniqueValues(
                array_column($options, 'fillLevel'),
                'Each container level sensor must have different fill level.' // i18n
            );
            Assertion::maxCount($options, 10, 'Container supports up to 10 sensors.'); // i18n
            $subject->setUserConfigValue('sensors', $options);
        }
        $availableFillLevels = array_merge([0], array_column($subject->getUserConfigValue('sensors', []), 'fillLevel'));
        if (ChannelFunctionBitsFlags::TANK_FILL_LEVEL_REPORTING_IN_FULL_RANGE()->isOn($subject->getFlags())) {
            $availableFillLevels = range(0, 100);
        }
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
            ChannelFunction::WATER_TANK,
        ]);
    }
}
