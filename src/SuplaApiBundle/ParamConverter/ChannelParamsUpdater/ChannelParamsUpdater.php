<?php
namespace SuplaApiBundle\ParamConverter\ChannelParamsUpdater;

use SuplaBundle\Entity\IODeviceChannel;

class ChannelParamsUpdater {
    /** @var SingleChannelParamsUpdater[] */
    private $updaters = [];

    public function registerChannelUpdater(SingleChannelParamsUpdater $updater) {
        $this->updaters[] = $updater;
    }

    public function updateChannelParams(IODeviceChannel $channel, IODeviceChannel $updatedChannel) {
        foreach ($this->updaters as $updater) {
            if ($updater->supports($channel)) {
                $updater->updateChannelParams($channel, $updatedChannel);
            }
        }
    }
}
