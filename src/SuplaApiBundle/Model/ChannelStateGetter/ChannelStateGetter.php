<?php
namespace SuplaApiBundle\Model\ChannelStateGetter;

use SuplaBundle\Entity\IODeviceChannel;

class ChannelStateGetter {
    /** @var SingleChannelStateGetter[] */
    private $stateGetters = [];

    public function __construct(iterable $stateGetters) {
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
}
