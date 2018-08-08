<?php
namespace SuplaBundle\Model\ChannelParamsUpdater;

use SuplaBundle\Enums\ChannelFunction;

class ControllingTheRoletteShutterOpeningTimer extends RangeParamsUpdater {
    const MAX_TIME_IN_SECONDS = 300;

    public function __construct() {
        parent::__construct(ChannelFunction::CONTROLLINGTHEROLLERSHUTTER(), 0, self::MAX_TIME_IN_SECONDS * 10);
    }
}
