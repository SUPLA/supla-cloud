<?php

namespace SuplaBundle\Model\UserConfigTranslator;

use SuplaBundle\Entity\HasUserConfig;
use SuplaBundle\Enums\ChannelFunction;

class NumberOfAttemptsToOpenOrCloseParamTranslator extends UserConfigTranslator {
    use FixedRangeParamsTranslator;

    private const MIN_ATTEMPTS = 1;
    private const MAX_ATTEMPTS = 5;
    private const DEFAULT_ATTEMPTS = self::MAX_ATTEMPTS;

    public function getConfig(HasUserConfig $subject): array {
        return [
            'numberOfAttemptsToOpen' => $subject->getUserConfigValue('numberOfAttemptsToOpen', self::DEFAULT_ATTEMPTS),
            'numberOfAttemptsToClose' => $subject->getUserConfigValue('numberOfAttemptsToClose', self::DEFAULT_ATTEMPTS),
            'stateVerificationMethodActive' => $subject->getUserConfigValue('stateVerificationMethodActive', false),
        ];
    }

    public function setConfig(HasUserConfig $subject, array $config) {
        $userConfig = $subject->getUserConfig();
        if (array_key_exists('numberOfAttemptsToOpen', $config) || !isset($userConfig['numberOfAttemptsToOpen'])) {
            $value = $config['numberOfAttemptsToOpen'] ?? self::DEFAULT_ATTEMPTS;
            $userConfig['numberOfAttemptsToOpen'] = intval($this->getValueInRange($value, self::MIN_ATTEMPTS, self::MAX_ATTEMPTS));
        }
        if (array_key_exists('numberOfAttemptsToClose', $config) || !isset($userConfig['numberOfAttemptsToClose'])) {
            $value = $config['numberOfAttemptsToClose'] ?? self::DEFAULT_ATTEMPTS;
            $userConfig['numberOfAttemptsToClose'] = intval($this->getValueInRange($value, self::MIN_ATTEMPTS, self::MAX_ATTEMPTS));
        }
        if (array_key_exists('stateVerificationMethodActive', $config) || !isset($userConfig['stateVerificationMethodActive'])) {
            $value = $config['stateVerificationMethodActive'] ?? false;
            $userConfig['stateVerificationMethodActive'] = boolval($value);
        }
        $subject->setUserConfig($userConfig);
    }

    public function supports(HasUserConfig $subject): bool {
        return in_array($subject->getFunction()->getId(), [
            ChannelFunction::CONTROLLINGTHEGARAGEDOOR,
            ChannelFunction::CONTROLLINGTHEGATE,
        ]);
    }
}
