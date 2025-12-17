<?php

namespace SuplaBundle\Model\UserConfigTranslator;

use SuplaBundle\Entity\HasUserConfig;
use SuplaBundle\Enums\ChannelFunction;

class ThermostatHeatpolHomePlusConfigTranslator extends UserConfigTranslator {
    public function getConfig(HasUserConfig $subject): array {
        return [
            'heatingModeAvailable' => true,
        ];
    }

    public function setConfig(HasUserConfig $subject, array $config) {
    }

    public function supports(HasUserConfig $subject): bool {
        return in_array($subject->getFunction()->getId(), [
            ChannelFunction::THERMOSTATHEATPOLHOMEPLUS,
        ]);
    }
}
