<?php
namespace SuplaApiBundle\Model\ChannelStateGetter;

use SuplaBundle\Entity\IODeviceChannel;

class ChannelStateGetter {
    /** @var SingleChannelStateGetter[] */
    private $updaters = [];

    public function registerChannelStateGetter(SingleChannelStateGetter $updater) {
        $this->updaters[] = $updater;
    }

    public function getState(IODeviceChannel $channel): array {
        $state = [];
        foreach ($this->updaters as $updater) {
            if (in_array($channel->getFunction(), $updater->supportedFunctions())) {
                $state = array_merge($state, $updater->getState($channel));
            }
        }
        return $state;
    }
}
