<?php

namespace App\Model\UserConfigTranslator;

use App\Entity\HasUserConfig;
use App\Entity\Main\IODeviceChannel;
use App\Enums\ChannelFlags;
use App\Enums\ChannelFunction;
use Assert\Assertion;

class ValveConfigTranslator extends UserConfigTranslator {
    public function __construct(private ChannelReferenceValidator $references) {
    }

    public function getConfig(HasUserConfig $subject): array {
        $config = [];
        if ($subject instanceof IODeviceChannel) {
            if (ChannelFlags::FLOOD_SENSORS_SUPPORTED()->isSupported($subject->getFlags())) {
                $config['floodSensorChannelIds'] = $subject->getUserConfigValue('floodSensorChannelIds', []);
            }
            if ($subject->getUserConfigValue('closeValveOnFloodType')) {
                $config['closeValveOnFloodType'] = $subject->getUserConfigValue('closeValveOnFloodType');
            }
        }
        $config['motorAlarmSupported'] = ChannelFlags::VALVE_MOTOR_ALARM_SUPPORTED()->isSupported($subject->getFlags());
        return $config;
    }

    public function setConfig(HasUserConfig $subject, array $config) {
        if (array_key_exists('floodSensorChannelIds', $config) && $config['floodSensorChannelIds'] !== null) {
            Assertion::true(
                ChannelFlags::FLOOD_SENSORS_SUPPORTED()->isSupported($subject->getFlags()),
                'Flood sesnors not supported in this channel.'
            );
            Assertion::isArray($config['floodSensorChannelIds'], null, 'floodSensorChannelIds');
            Assertion::true(array_is_list($config['floodSensorChannelIds']), 'Expected a list.', 'floodSensorChannelIds');
            Assertion::maxCount($config['floodSensorChannelIds'], 20, 'Valve supports up to 20 sensors.'); // i18n
            foreach ($config['floodSensorChannelIds'] as $id) {
                if ($id !== null) {
                    Assertion::integer($id, null, 'floodSensorChannelIds');
                    $this->references->resolve($subject, $id, 'floodSensorChannelIds');
                }
            }
            $subject->setUserConfigValue('floodSensorChannelIds', $config['floodSensorChannelIds']);
        }
        if ($config['closeValveOnFloodType'] ?? false) {
            Assertion::keyExists($this->getConfig($subject), 'closeValveOnFloodType', 'Cannot set close type for this channel.');
            Assertion::inArray($config['closeValveOnFloodType'], ['ALWAYS', 'ON_CHANGE']);
            $subject->setUserConfigValue('closeValveOnFloodType', $config['closeValveOnFloodType']);
        }
    }

    public function supports(HasUserConfig $subject): bool {
        return in_array($subject->getFunction()->getId(), [
            ChannelFunction::VALVEOPENCLOSE,
        ]);
    }
}
