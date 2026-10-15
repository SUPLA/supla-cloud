<?php

namespace App\Model\UserConfigTranslator;

use App\Entity\Main\IODeviceChannel;

/** Deterministic compatibility boundary for legacy, destination-local HVAC references. */
final class HvacChannelReferences {
    public const FIELDS = [
        'mainThermometer', 'auxThermometer', 'binarySensor',
        'masterThermostat', 'pumpSwitch', 'heatOrColdSourceSwitch',
    ];

    public static function canonicalConfig(IODeviceChannel $subject): array {
        $config = $subject->getUserConfig();
        foreach (self::FIELDS as $field) {
            $legacyKey = $field . 'ChannelNo';
            $canonicalKey = $field . 'ChannelId';
            if (!array_key_exists($legacyKey, $config)) {
                continue;
            }
            if (!array_key_exists($canonicalKey, $config)) {
                $config[$canonicalKey] = null;
                $number = $config[$legacyKey];
                if (is_int($number) && $number >= 0) {
                    foreach ($subject->getIoDevice()->getChannels() as $channel) {
                        if ($channel->getChannelNumber() === $number) {
                            // Legacy Main used the destination's own number to mean unset.
                            if ($field !== 'mainThermometer' || $channel->getId() !== $subject->getId()) {
                                $config[$canonicalKey] = $channel->getId();
                            }
                            break;
                        }
                    }
                }
            }
            unset($config[$legacyKey]);
        }
        return $config;
    }

    public static function migrate(IODeviceChannel $subject): bool {
        $config = self::canonicalConfig($subject);
        if ($config === $subject->getUserConfig()) {
            return false;
        }
        $subject->setUserConfig($config);
        return true;
    }
}
