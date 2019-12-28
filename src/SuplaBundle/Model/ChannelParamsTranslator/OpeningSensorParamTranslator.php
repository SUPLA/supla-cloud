<?php

namespace SuplaBundle\Model\ChannelParamsTranslator;

use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;

class OpeningSensorParamTranslator implements ChannelParamTranslator {
    /** @var ControllingAnyLockRelatedSensorUpdater */
    private $updater;

    public function __construct(ControllingAnyLockRelatedSensorUpdater $updater) {
        $this->updater = $updater;
    }

    public function getConfigFromParams(IODeviceChannel $channel): array {
        return ['openingSensorChannelId' => $channel->getParam2() ?: null];
    }

    public function setParamsFromConfig(IODeviceChannel $channel, array $config) {
        if (array_key_exists('openingSensorChannelId', $config)) {
            $this->updater->pairControllingAndSensorChannels(
                $channel->getFunction(),
                new ChannelFunction(
                    array_search($channel->getFunction()->getId(), ControllingChannelParamTranslator::SENSOR_CONTROLLING_PAIRS)
                ),
                2,
                1,
                $channel->getId(),
                intval($config['openingSensorChannelId'])
            );
        }
    }

    public function supports(IODeviceChannel $channel): bool {
        return in_array($channel->getFunction()->getId(), [
            ChannelFunction::CONTROLLINGTHEDOORLOCK,
            ChannelFunction::CONTROLLINGTHEGARAGEDOOR,
            ChannelFunction::CONTROLLINGTHEGATE,
            ChannelFunction::CONTROLLINGTHEGATEWAYLOCK,
            ChannelFunction::CONTROLLINGTHEROLLERSHUTTER,
        ]);
    }
}
