<?php

namespace SuplaBundle\Model\UserConfigTranslator;

use Assert\Assertion;
use SuplaBundle\Entity\HasUserConfig;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Utils\NumberUtils;

class OvercurrentThresholdConfigTranslator extends UserConfigTranslator {
    use FixedRangeParamsTranslator;

    public function getConfig(HasUserConfig $subject): array {
        $config = [];
        if ($subject->getProperty('overcurrentMaxAllowed')) {
            $config['overcurrentMaxAllowed'] = $subject->getProperty('overcurrentMaxAllowed') / 100;
            $threshold = $subject->getUserConfigValue('overcurrentThreshold', 0);
            $config['overcurrentThreshold'] = NumberUtils::maximumDecimalPrecision($threshold / 100);
        }
        return $config;
    }

    public function setConfig(HasUserConfig $subject, array $config) {
        if (array_key_exists('overcurrentThreshold', $config)) {
            $currentConfig = $this->getConfig($subject);
            Assertion::keyExists($currentConfig, 'overcurrentMaxAllowed');
            if ($config['overcurrentThreshold']) {
                Assertion::numeric($config['overcurrentThreshold']);
                $threshold = floatval($config['overcurrentThreshold']);
                Assertion::between($threshold, 0, $currentConfig['overcurrentMaxAllowed']);
                $subject->setUserConfigValue('overcurrentThreshold', round($threshold * 100));
            } else {
                $subject->setUserConfigValue('overcurrentThreshold', 0);
            }
        }
    }

    public function supports(HasUserConfig $subject): bool {
        return in_array($subject->getFunction()->getId(), [
            ChannelFunction::LIGHTSWITCH,
            ChannelFunction::POWERSWITCH,
            ChannelFunction::STAIRCASETIMER,
        ]);
    }
}
