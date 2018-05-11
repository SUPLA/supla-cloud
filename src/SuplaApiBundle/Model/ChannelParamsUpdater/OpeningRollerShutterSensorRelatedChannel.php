<?php
namespace SuplaApiBundle\Model\ChannelParamsUpdater;

use SuplaBundle\Enums\ChannelFunction;

class OpeningRollerShutterSensorRelatedChannel extends OpeningAnySensorRelatedChannel {
    public function __construct() {
        parent::__construct(ChannelFunction::CONTROLLINGTHEROLLERSHUTTER(), ChannelFunction::OPENINGSENSOR_ROLLERSHUTTER());
    }
}
