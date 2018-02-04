<?php
namespace SuplaApiBundle\ParamConverter\ChannelParamsUpdater;

use SuplaBundle\Enums\ChannelFunction;

class ControllingTheGateRelatedSensor extends ControllingAnyLockRelatedSensor {
    public function __construct() {
        parent::__construct(ChannelFunction::CONTROLLINGTHEGATE(), ChannelFunction::OPENINGSENSOR_GATE());
    }
}
