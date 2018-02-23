<?php
namespace SuplaApiBundle\Model\ChannelParamsUpdater;

use SuplaBundle\Enums\ChannelFunction;

class ControllingTheDoorLockTime extends RangeParamsUpdater {
    public function __construct() {
        parent::__construct(ChannelFunction::CONTROLLINGTHEDOORLOCK(), 500, 10000);
    }
}
