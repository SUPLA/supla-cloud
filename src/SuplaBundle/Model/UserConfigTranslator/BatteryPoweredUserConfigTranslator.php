<?php

namespace SuplaBundle\Model\UserConfigTranslator;

use SuplaBundle\Entity\HasUserConfig;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Model\ChannelStateGetter\ExtendedChannelStateGetter;

class BatteryPoweredUserConfigTranslator extends UserConfigTranslator {
    private ExtendedChannelStateGetter $extendedChannelStateGetter;

    public function __construct(ExtendedChannelStateGetter $extendedChannelStateGetter) {
        $this->extendedChannelStateGetter = $extendedChannelStateGetter;
    }

    public function getConfig(HasUserConfig $subject): array {
        return [
            'isBatteryPowered' => $this->extendedChannelStateGetter->getState($subject)['isBatteryPowered'],
        ];
    }

    public function setConfig(HasUserConfig $subject, array $config) {
    }

    public function supports(HasUserConfig $subject): bool {
        return in_array($subject->getFunction()->getId(), [
            ChannelFunction::HVAC_THERMOSTAT,
            ChannelFunction::HVAC_THERMOSTAT_HEAT_COOL,
            ChannelFunction::HVAC_THERMOSTAT_DIFFERENTIAL,
            ChannelFunction::HVAC_DOMESTIC_HOT_WATER,
        ]);
    }
}
