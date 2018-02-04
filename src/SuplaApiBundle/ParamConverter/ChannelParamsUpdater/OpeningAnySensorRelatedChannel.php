<?php
namespace SuplaApiBundle\ParamConverter\ChannelParamsUpdater;

use SuplaBundle\Entity\IODeviceChannel;

abstract class OpeningAnySensorRelatedChannel extends ControllingAnyLockRelatedSensor {
    public function updateChannelParams(IODeviceChannel $channel, IODeviceChannel $updatedChannel) {
        $this->pairControllingAndSensorChannels($updatedChannel->getParam1(), $channel->getId());
    }

    public function supports(IODeviceChannel $channel): bool {
        return $channel->getFunction() == $this->sensorFunction;
    }
}
