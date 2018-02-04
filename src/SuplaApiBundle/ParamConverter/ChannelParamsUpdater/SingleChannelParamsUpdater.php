<?php
namespace SuplaApiBundle\ParamConverter\ChannelParamsUpdater;

use SuplaBundle\Entity\IODeviceChannel;

interface SingleChannelParamsUpdater {
    public function updateChannelParams(IODeviceChannel $channel, IODeviceChannel $updatedChannel);

    public function supports(IODeviceChannel $channel): bool;
}
