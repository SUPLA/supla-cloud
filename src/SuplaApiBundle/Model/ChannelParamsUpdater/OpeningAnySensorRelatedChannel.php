<?php
namespace SuplaApiBundle\Model\ChannelParamsUpdater;

use SuplaBundle\Entity\IODeviceChannel;

abstract class OpeningAnySensorRelatedChannel extends ControllingAnyLockRelatedSensor {
    public function updateChannelParams(IODeviceChannel $channel, IODeviceChannel $updatedChannel) {
        $this->pairControllingAndSensorChannels($updatedChannel->getParam($this->sensorParamNo), $channel->getId());
    }

    public function supports(IODeviceChannel $channel): bool {
        return $channel->getFunction() == $this->sensorFunction;
    }
}
