<?php
namespace SuplaApiBundle\Model\ChannelParamsUpdater;

use SuplaBundle\Enums\ChannelFunction;

class ControllingTheDoorLockRelatedSensor extends ControllingAnyLockRelatedSensor {
    public function __construct() {
        parent::__construct(ChannelFunction::CONTROLLINGTHEDOORLOCK(), ChannelFunction::OPENINGSENSOR_DOOR());
    }
}
