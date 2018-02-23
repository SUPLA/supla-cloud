<?php
namespace SuplaApiBundle\Model\ChannelParamsUpdater;

use SuplaBundle\Enums\ChannelFunction;

class ControllingTheRoletteShutterOpeningTimer extends RangeParamsUpdater {
    public function __construct() {
        parent::__construct(ChannelFunction::CONTROLLINGTHEROLLERSHUTTER(), 0, 3000);
    }
}
