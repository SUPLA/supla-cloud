<?php
namespace SuplaBundle\Model\ChannelStateGetter;

use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Entity\IODeviceChannelGroup;

class ChannelStateGetter {
    /** @var SingleChannelStateGetter[] */
    private $stateGetters = [];

    public function __construct($stateGetters) {
        $this->stateGetters = $stateGetters;
    }

    public function getState(IODeviceChannel $channel): array {
        $state = [];
        foreach ($this->stateGetters as $stateGetter) {
            if (in_array($channel->getFunction(), $stateGetter->supportedFunctions())) {
                $state = array_merge($state, $stateGetter->getState($channel));
            }
        }
        return $state;
    }

    public function getStateForChannelGroup(IODeviceChannelGroup $group): array {
        $state = [];
        foreach ($group->getChannels() as $channel) {
            $state[$channel->getId()] = $this->getState($channel);
        }
        return $state;
    }
}
