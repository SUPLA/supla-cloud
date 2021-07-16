<?php
namespace SuplaBundle\Model\ChannelStateGetter;

use InvalidArgumentException;
use SuplaBundle\Entity\HasFunction;
use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Entity\IODeviceChannelGroup;
use SuplaBundle\Supla\SuplaServerIsDownException;

class ChannelStateGetter {
    /** @var SingleChannelStateGetter[] */
    private $stateGetters = [];

    public function __construct($stateGetters) {
        $this->stateGetters = $stateGetters;
    }

    public function getState(HasFunction $channel): array {
        if ($channel instanceof IODeviceChannel) {
            $state = [];
            try {
                foreach ($this->stateGetters as $stateGetter) {
                    if (in_array($channel->getFunction(), $stateGetter->supportedFunctions())) {
                        $state = array_merge($state, $stateGetter->getState($channel));
                    }
                }
            } catch (SuplaServerIsDownException $e) {
                $state = ['connected' => false];
            }
            return $state;
        } elseif ($channel instanceof IODeviceChannelGroup) {
            return $this->getStateForChannelGroup($channel);
        } else {
            throw new InvalidArgumentException('Could not get state for entity ' . get_class($channel));
        }
    }

    public function getStateForChannelGroup(IODeviceChannelGroup $group): array {
        $state = [];
        foreach ($group->getChannels() as $channel) {
            $state[$channel->getId()] = $this->getState($channel);
        }
        return $state;
    }
}
