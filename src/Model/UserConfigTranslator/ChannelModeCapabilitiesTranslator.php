<?php

namespace App\Model\UserConfigTranslator;

use App\Entity\HasUserConfig;
use App\Entity\Main\IODeviceChannel;
use App\Enums\ChannelFlags;
use App\Enums\ChannelFunction;

class ChannelModeCapabilitiesTranslator extends UserConfigTranslator {
    private const RELAY_FUNCTIONS = [
        ChannelFunction::LIGHTSWITCH,
        ChannelFunction::POWERSWITCH,
        ChannelFunction::STAIRCASETIMER,
        ChannelFunction::CONTROLLINGTHEGATE,
        ChannelFunction::CONTROLLINGTHEDOORLOCK,
        ChannelFunction::CONTROLLINGTHEGARAGEDOOR,
        ChannelFunction::CONTROLLINGTHEGATEWAYLOCK,
    ];

    public function getConfig(HasUserConfig $subject): array {
        if (!$subject instanceof IODeviceChannel) {
            return [];
        }
        $flags = $subject->getFlags();
        if ($subject->getFunction()->getId() === ChannelFunction::ACTION_TRIGGER) {
            return [
                'buttonModeSupported' => ChannelFlags::BUTTON_MODE_SUPPORTED()->isSupported($flags),
                'buttonTemporaryUnlockSupported' => ChannelFlags::BUTTON_TEMPORARY_UNLOCK_SUPPORTED()->isSupported($flags),
            ];
        }
        if (in_array($subject->getFunction()->getId(), self::RELAY_FUNCTIONS, true)) {
            return [
                'relayModeStartSupported' => ChannelFlags::RELAY_MODE_START_SUPPORTED()->isSupported($flags),
                'relayModeForcedSupported' => ChannelFlags::RELAY_MODE_FORCED_SUPPORTED()->isSupported($flags),
                'relayModeAutomaticSupported' => ChannelFlags::RELAY_MODE_AUTOMATIC_SUPPORTED()->isSupported($flags),
                'relayModeNotSetSupported' => ChannelFlags::RELAY_MODE_NOT_SET_SUPPORTED()->isSupported($flags),
            ];
        }
        return [];
    }

    public function setConfig(HasUserConfig $subject, array $config) {
        // Capabilities are supplied by the device, never by API clients.
    }

    public function supports(HasUserConfig $subject): bool {
        return $subject instanceof IODeviceChannel
            && ($subject->getFunction()->getId() === ChannelFunction::ACTION_TRIGGER
                || in_array($subject->getFunction()->getId(), self::RELAY_FUNCTIONS, true));
    }
}
