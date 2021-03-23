<?php
namespace SuplaBundle\Model\ChannelParamsUpdater;

use SuplaBundle\Enums\ChannelFunction;

class ElectricityMeterRelatedChannel extends OpeningAnySensorRelatedChannel {
    public function __construct() {
        parent::__construct(ChannelFunction::ELECTRICITYMETER(), ChannelFunction::POWERSWITCH(), 4, 1);
    }
}
