<?php
namespace SuplaApiBundle\Model\ChannelParamsUpdater;

use SuplaBundle\Enums\ChannelFunction;

class OpeningDoorSensorRelatedChannel extends OpeningAnySensorRelatedChannel {
    public function __construct() {
        parent::__construct(ChannelFunction::CONTROLLINGTHEDOORLOCK(), ChannelFunction::OPENINGSENSOR_DOOR());
    }
}
