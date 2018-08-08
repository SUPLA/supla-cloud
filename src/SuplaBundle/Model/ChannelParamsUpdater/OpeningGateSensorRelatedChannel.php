<?php
namespace SuplaBundle\Model\ChannelParamsUpdater;

use SuplaBundle\Enums\ChannelFunction;

class OpeningGateSensorRelatedChannel extends OpeningAnySensorRelatedChannel {
    public function __construct() {
        parent::__construct(ChannelFunction::CONTROLLINGTHEGATE(), ChannelFunction::OPENINGSENSOR_GATE());
    }
}
