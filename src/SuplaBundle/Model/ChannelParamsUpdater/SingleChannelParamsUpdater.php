<?php
namespace SuplaBundle\Model\ChannelParamsUpdater;

use SuplaBundle\Entity\Main\IODeviceChannel;

interface SingleChannelParamsUpdater {
    public function updateChannelParams(IODeviceChannel $channel, IODeviceChannel $updatedChannel);

    public function supports(IODeviceChannel $channel): bool;
}
