<?php

namespace SuplaBundle\Model\ChannelParamsTranslator;

use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;

class NumberOfAttemptsToOpenOrCloseParamTranslator implements ChannelParamTranslator {
    use FixedRangeParamsTranslator;

    private const MIN_ATTEMPTS = 1;
    private const MAX_ATTEMPTS = 5;
    private const DEFAULT_ATTEMPTS = self::MAX_ATTEMPTS;

    public function getConfigFromParams(IODeviceChannel $channel): array {
        return [
            'numberOfAttemptsToOpen' => $channel->getUserConfigValue('numberOfAttemptsToOpen', self::DEFAULT_ATTEMPTS),
            'numberOfAttemptsToClose' => $channel->getUserConfigValue('numberOfAttemptsToClose', self::DEFAULT_ATTEMPTS),
            'stateVerificationMethodActive' => $channel->getUserConfigValue('stateVerificationMethodActive', false),
        ];
    }

    public function setParamsFromConfig(IODeviceChannel $channel, array $config) {
        $userConfig = $channel->getUserConfig();
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
        $channel->setUserConfig($userConfig);
    }

    public function supports(IODeviceChannel $channel): bool {
        return in_array($channel->getFunction()->getId(), [
            ChannelFunction::CONTROLLINGTHEGARAGEDOOR,
            ChannelFunction::CONTROLLINGTHEGATE,
        ]);
    }
}
