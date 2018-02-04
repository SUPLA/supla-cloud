<?php
namespace SuplaApiBundle\ParamConverter\ChannelParamsUpdater;

use SuplaBundle\Enums\ChannelFunction;

class ControllingTheGarageDoorRelatedSensor extends ControllingAnyLockRelatedSensor {
    public function __construct() {
        parent::__construct(ChannelFunction::CONTROLLINGTHEGARAGEDOOR(), ChannelFunction::OPENINGSENSOR_GARAGEDOOR());
    }
}
