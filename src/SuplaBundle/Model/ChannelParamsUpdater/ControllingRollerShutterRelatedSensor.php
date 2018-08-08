<?php
namespace SuplaBundle\Model\ChannelParamsUpdater;

use SuplaBundle\Enums\ChannelFunction;

class ControllingRollerShutterRelatedSensor extends ControllingAnyLockRelatedSensor {
    public function __construct() {
        parent::__construct(ChannelFunction::CONTROLLINGTHEROLLERSHUTTER(), ChannelFunction::OPENINGSENSOR_ROLLERSHUTTER());
    }
}
