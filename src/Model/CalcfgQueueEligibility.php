<?php
/*
 Copyright (C) AC SOFTWARE SP. Z O.O.

 This program is free software; you can redistribute it and/or
 modify it under the terms of the GNU General Public License
 as published by the Free Software Foundation; either version 2
 of the License, or (at your option) any later version.
 */

namespace App\Model;

use App\Entity\Main\IODevice;
use App\Entity\Main\IODeviceChannel;
use App\Enums\ChannelFunction;

/** Backend-derived offline queue eligibility. This is not delivery assurance. */
final class CalcfgQueueEligibility {
    /** @return array<string, bool> */
    public static function forDevice(IODevice $device): array {
        $flags = $device->getFlags();
        $supported = [
            'enterConfigurationMode' => $flags['enterConfigurationModeAvailable'],
            'restartDevice' => $flags['remoteRestartAvailable'],
            'otaCheckUpdates' => $flags['automaticFirmwareUpdatesSupported'],
            'otaPerformUpdate' => $flags['automaticFirmwareUpdatesSupported'],
            'factoryReset' => $flags['factoryResetSupported'],
            'setCfgModePassword' => $flags['setCfgModePasswordSupported'],
            'identifyDevice' => $flags['identifyDeviceAvailable'],
        ];

        foreach ($supported as $action => $hasSupport) {
            $supported[$action] = $hasSupport && $device->isSleepModeEnabled()
                && ($device->isSyncDoneSupported() || in_array($action, [
                    'enterConfigurationMode', 'restartDevice', 'identifyDevice',
                ], true));
        }
        return $supported;
    }

    /** @return array<string, bool> */
    public static function forChannel(IODeviceChannel $channel, bool $muteAlarmSupported, bool $ocrSupported): array {
        $device = $channel->getIoDevice();
        $syncQueueAvailable = $device->isSleepModeEnabled() && $device->isSyncDoneSupported();
        $flags = $channel->getFlags();

        return [
            'muteAlarm' => $syncQueueAvailable && $muteAlarmSupported,
            'ocr:takePhoto' => $syncQueueAvailable && $ocrSupported,
            // Core's calcfg queue accepts IDENTIFY_DEVICE/RESTART_DEVICE, but
            // not the distinct IDENTIFY_SUBDEVICE/RESTART_SUBDEVICE commands.
            'identify' => false,
            'restart' => false,
        ];
    }

    public static function supportsMuteAlarm(IODeviceChannel $channel): bool {
        return in_array($channel->getFunction()->getValue(), [
            ChannelFunction::CONTAINER,
            ChannelFunction::SEPTIC_TANK,
            ChannelFunction::WATER_TANK,
        ], true);
    }
}
