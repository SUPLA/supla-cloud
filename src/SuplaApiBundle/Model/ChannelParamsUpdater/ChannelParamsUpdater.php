<?php
namespace SuplaApiBundle\Model\ChannelParamsUpdater;

use SuplaBundle\Entity\IODeviceChannel;

class ChannelParamsUpdater {
    /** @var SingleChannelParamsUpdater[] */
    private $updaters = [];

    public function __construct($updaters) {
        $this->updaters = $updaters;
    }

    public function updateChannelParams(IODeviceChannel $channel, IODeviceChannel $updatedChannel) {
        foreach ($this->updaters as $updater) {
            if ($updater->supports($channel)) {
                $updater->updateChannelParams($channel, $updatedChannel);
            }
        }
    }
}
