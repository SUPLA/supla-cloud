<?php

namespace SuplaBundle\Model\ChannelParamsTranslator;

use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;

class ControllingChannelParamTranslator implements ChannelParamTranslator {
    const SENSOR_CONTROLLING_PAIRS = [
        ChannelFunction::OPENINGSENSOR_DOOR => ChannelFunction::CONTROLLINGTHEDOORLOCK,
        ChannelFunction::OPENINGSENSOR_GARAGEDOOR => ChannelFunction::CONTROLLINGTHEGARAGEDOOR,
        ChannelFunction::OPENINGSENSOR_GATE => ChannelFunction::CONTROLLINGTHEGATE,
        ChannelFunction::OPENINGSENSOR_GATEWAY => ChannelFunction::CONTROLLINGTHEGATEWAYLOCK,
        ChannelFunction::OPENINGSENSOR_ROLLERSHUTTER => ChannelFunction::CONTROLLINGTHEROLLERSHUTTER,
    ];

    /** @var ControllingAnyLockRelatedSensorUpdater */
    private $updater;

    public function __construct(ControllingAnyLockRelatedSensorUpdater $updater) {
        $this->updater = $updater;
    }

    public function getConfigFromParams(IODeviceChannel $channel): array {
        return ['controllingChannelId' => $channel->getParam1()];
    }

    public function setParamsFromConfig(IODeviceChannel $channel, array $config) {
        if (array_key_exists('controllingChannelId', $config)) {
            $this->updater->pairControllingAndSensorChannels(
                new ChannelFunction(self::SENSOR_CONTROLLING_PAIRS[$channel->getFunction()->getId()]),
                $channel->getFunction(),
                2,
                1,
                intval($config['controllingChannelId']),
                $channel->getId()
            );
        }
    }

    public function supports(IODeviceChannel $channel): bool {
        return in_array($channel->getFunction()->getId(), [
            ChannelFunction::OPENINGSENSOR_DOOR,
            ChannelFunction::OPENINGSENSOR_GARAGEDOOR,
            ChannelFunction::OPENINGSENSOR_GATE,
            ChannelFunction::OPENINGSENSOR_GATEWAY,
            ChannelFunction::OPENINGSENSOR_ROLLERSHUTTER,
        ]);
    }
}
