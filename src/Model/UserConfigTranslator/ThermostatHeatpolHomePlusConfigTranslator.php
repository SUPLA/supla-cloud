<?php

namespace App\Model\UserConfigTranslator;

use App\Entity\HasUserConfig;
use App\Enums\ChannelFunction;

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
