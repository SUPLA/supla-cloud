<?php
namespace SuplaBundle\Model\ChannelParamsUpdater;

use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;

class OpeningGateSecondarySensorRelatedChannel extends OpeningAnySensorRelatedChannel {
    public function __construct() {
        parent::__construct(ChannelFunction::CONTROLLINGTHEGATE(), ChannelFunction::OPENINGSENSOR_GATE(), 3, 2);
    }

    public function updateChannelParams(IODeviceChannel $channel, IODeviceChannel $updatedChannel) {
        if ($updatedChannel->getParam1() == $updatedChannel->getParam2()) { // if primary and secondary channel the same, clear secondary
            $updatedChannel->setParam2(0);
        }
        parent::updateChannelParams($channel, $updatedChannel);
    }
}
