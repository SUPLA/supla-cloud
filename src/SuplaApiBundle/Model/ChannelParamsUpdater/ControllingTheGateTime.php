<?php
namespace SuplaApiBundle\Model\ChannelParamsUpdater;

use SuplaBundle\Enums\ChannelFunction;

class ControllingTheGateTime extends RangeParamsUpdater {
    public function __construct() {
        parent::__construct(ChannelFunction::CONTROLLINGTHEGATE(), 500, 2000);
    }
}
