<?php
namespace SuplaBundle\Model\ChannelParamsUpdater;

use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;

class ControllingTheGateSecondaryRelatedSensor extends ControllingAnyLockRelatedSensor {
    public function __construct() {
        parent::__construct(ChannelFunction::CONTROLLINGTHEGATE(), ChannelFunction::OPENINGSENSOR_GATE(), 3, 2);
    }

    public function updateChannelParams(IODeviceChannel $channel, IODeviceChannel $updatedChannel) {
        if ($updatedChannel->getParam2() == $updatedChannel->getParam3()) { // primary and secondary sensors the same, clear secondary
            $updatedChannel->setParam3(0);
        }
        parent::updateChannelParams($channel, $updatedChannel);
    }
}
