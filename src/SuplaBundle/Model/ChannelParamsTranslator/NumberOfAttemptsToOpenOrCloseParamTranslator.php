<?php

namespace SuplaBundle\Model\ChannelParamsTranslator;

use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;

class NumberOfAttemptsToOpenOrCloseParamTranslator implements ChannelParamTranslator {
    use FixedRangeParamsTranslator;

    private const MIN_ATTEMPTS = 1;
    private const MAX_ATTEMPTS = 5;
    private const DEFAULT_ATTEMPTS = self::MAX_ATTEMPTS;

    public function getConfigFromParams(IODeviceChannel $channel): array {
        return [
            'numberOfAttemptsToOpenOrClose' => $channel->getUserConfig()['numberOfAttemptsToOpenOrClose'] ?? self::DEFAULT_ATTEMPTS,
        ];
    }

    public function setParamsFromConfig(IODeviceChannel $channel, array $config) {
        $userConfig = $channel->getUserConfig();
        if (array_key_exists('numberOfAttemptsToOpenOrClose', $config) || !isset($userConfig['numberOfAttemptsToOpenOrClose'])) {
            $value = $config['numberOfAttemptsToOpenOrClose'] ?? self::DEFAULT_ATTEMPTS;
            $userConfig['numberOfAttemptsToOpenOrClose'] = intval($this->getValueInRange($value, self::MIN_ATTEMPTS, self::MAX_ATTEMPTS));
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
