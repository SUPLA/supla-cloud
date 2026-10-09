<?php

namespace App\Model\UserConfigTranslator;

use App\Entity\HasUserConfig;
use App\Enums\ChannelFlags;
use App\Enums\ChannelFunction;
use Assert\Assert;
use Assert\Assertion;

class TankConfigTranslator extends UserConfigTranslator {
    public function __construct(private ChannelReferenceValidator $references) {
    }

    public function getConfig(HasUserConfig $subject): array {
        $levelSensors = $this->getSensorSlots($subject);
        return [
            'levelSensors' => $levelSensors,
            'levelSensorChannelIds' => array_column($levelSensors, 'channelId'),
            'warningAboveLevel' => $subject->getUserConfigValue('warningAboveLevel'),
            'alarmAboveLevel' => $subject->getUserConfigValue('alarmAboveLevel'),
            'warningBelowLevel' => $subject->getUserConfigValue('warningBelowLevel'),
            'alarmBelowLevel' => $subject->getUserConfigValue('alarmBelowLevel'),
            'muteAlarmSoundWithoutAdditionalAuth' => boolval($subject->getUserConfigValue('muteAlarmSoundWithoutAdditionalAuth')),
            'fillLevelReportingInFullRange' =>
                ChannelFlags::TANK_FILL_LEVEL_REPORTING_IN_FULL_RANGE()->isOn($subject->getFlags()),
        ];
    }

    public function setConfig(HasUserConfig $subject, array $config) {
        if (
            array_key_exists('levelSensorChannelIds', $config)
            && !array_key_exists('levelSensors', $config)
            && $config['levelSensorChannelIds'] !== null
        ) {
            // request from ChannelDependencies clearing
            Assertion::isArray($config['levelSensorChannelIds'], null, 'levelSensorChannelIds');
            // Preserve independent slots and scalar settings during reverse cleanup.
            $sensors = $this->getSensorSlots($subject);
            foreach ($sensors as &$sensor) {
                if (is_array($sensor) && !in_array($sensor['channelId'] ?? null, $config['levelSensorChannelIds'], true)) {
                    $sensor['channelId'] = null;
                }
            }
            unset($sensor);
            $subject->setUserConfigValue('sensors', $sensors);
        }
        if (array_key_exists('levelSensors', $config) && $config['levelSensors'] !== null) {
            Assertion::isArray($config['levelSensors'], null, 'levelSensors');
            Assertion::true(array_is_list($config['levelSensors']), 'Expected a list.', 'levelSensors');
            $options = array_map(function ($lvlConfig) use ($subject) {
                if ($lvlConfig === null) {
                    return null; // Explicit unconfigured slot.
                }
                Assertion::isArray($lvlConfig, null, 'levelSensors');
                Assertion::keyExists($lvlConfig, 'channelId', null, 'levelSensors');
                $id = $lvlConfig['channelId'];
                if ($id !== null) {
                    Assertion::integer($id, null, 'levelSensors');
                    $this->references->resolve($subject, $id, 'levelSensorChannelIds');
                    Assertion::keyExists($lvlConfig, 'fillLevel', null, 'levelSensors');
                }
                // Preserve metadata attached to an independently configured slot.
                unset($lvlConfig['channelNo']);
                return $lvlConfig;
            }, $config['levelSensors']);
            $configuredSlots = array_filter($options, fn($slot) => is_array($slot) && ($slot['channelId'] ?? null) !== null);
            Assert::thatAll(array_column($configuredSlots, 'fillLevel'), null, 'levelSensors.fillLevel')
                ->integer()->between(1, 100);
            Assertion::uniqueValues(
                array_column($configuredSlots, 'fillLevel'),
                'Each container level sensor must have different fill level.' // i18n
            );
            Assertion::maxCount($options, 10, 'Container supports up to 10 sensors.'); // i18n
            $subject->setUserConfigValue('sensors', $options);
        }
        $availableFillLevels = array_merge([0], array_column($this->getSensorSlots($subject), 'fillLevel'));
        if (ChannelFlags::TANK_FILL_LEVEL_REPORTING_IN_FULL_RANGE()->isOn($subject->getFlags())) {
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
        if ($subject->getUserConfigValue('warningAboveLevel') !== null && $subject->getUserConfigValue('alarmAboveLevel') !== null) {
            Assertion::greaterThan(
                $subject->getUserConfigValue('alarmAboveLevel'),
                $subject->getUserConfigValue('warningAboveLevel'),
                'Alarm at level (and above) should be greater than the corresponding warning level.' // i18n
            );
        }
        if ($subject->getUserConfigValue('warningBelowLevel') !== null && $subject->getUserConfigValue('alarmBelowLevel') !== null) {
            Assertion::lessThan(
                $subject->getUserConfigValue('alarmBelowLevel'),
                $subject->getUserConfigValue('warningBelowLevel'),
                'Alarm at level (and below) should be lower than the corresponding warning level.' // i18n
            );
        }
        foreach (['warningBelowLevel', 'alarmBelowLevel'] as $below) {
            if ($subject->getUserConfigValue($below) !== null) {
                foreach (['warningAboveLevel', 'alarmAboveLevel'] as $above) {
                    if ($subject->getUserConfigValue($above) !== null) {
                        Assertion::greaterThan(
                            $subject->getUserConfigValue($above),
                            $subject->getUserConfigValue($below),
                            'Warning and alarm at level (and above) should be higher than the warning and alarm at level (and below).' // i18n
                        );
                    }
                }
            }
        }
        if (array_key_exists('muteAlarmSoundWithoutAdditionalAuth', $config)) {
            $subject->setUserConfigValue('muteAlarmSoundWithoutAdditionalAuth', boolval($config['muteAlarmSoundWithoutAdditionalAuth']));
        }
    }

    private function getSensorSlots(HasUserConfig $subject): array {
        $sensors = $subject->getUserConfigValue('sensors', []);
        // Read malformed historical arrays as unset without overwriting their diagnostic payload.
        return is_array($sensors) && array_is_list($sensors) ? $sensors : [];
    }

    public function supports(HasUserConfig $subject): bool {
        return in_array($subject->getFunction()->getId(), [
            ChannelFunction::CONTAINER,
            ChannelFunction::SEPTIC_TANK,
            ChannelFunction::WATER_TANK,
        ]);
    }
}
