<?php

namespace SuplaBundle\Model\ChannelParamsTranslator;

use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;

class OpeningSensorSecondaryParamTranslator implements ChannelParamTranslator {
    use ControllingAnyLockRelatedSensor;

    public function getConfigFromParams(IODeviceChannel $channel): array {
        return ['openingSensorSecondaryChannelId' => $channel->getParam3()];
    }

    public function setParamsFromConfig(IODeviceChannel $channel, array $config) {
        if (array_key_exists('openingSensorSecondaryChannelId', $config)) {
            if ($channel->getParam2() == $config['openingSensorSecondaryChannelId']) {
                // primary and secondary sensors the same, clear secondary
                $config['openingSensorSecondaryChannelId'] = 0;
            }
            $this->pairControllingAndSensorChannels(
                $channel->getFunction(),
                new ChannelFunction(
                    array_search($channel->getFunction()->getId(), ControllingChannelParamTranslator::SENSOR_CONTROLLING_PAIRS)
                ),
                3,
                2,
                $channel->getId(),
                intval($config['openingSensorSecondaryChannelId'])
            );
        }
    }

    public function supports(IODeviceChannel $channel): bool {
        return in_array($channel->getFunction()->getId(), [
            ChannelFunction::CONTROLLINGTHEGATE,
        ]);
    }
}
