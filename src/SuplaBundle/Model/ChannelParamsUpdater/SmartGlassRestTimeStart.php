<?php
namespace SuplaBundle\Model\ChannelParamsUpdater;

use SuplaBundle\Enums\ChannelFunction;

class SmartGlassRestTimeStart extends RangeParamsUpdater {
    const MINUTES_IN_DAY = 1440;

    public function __construct() {
        parent::__construct(
            [ChannelFunction::DIGIGLASS_VERTICAL(), ChannelFunction::DIGIGLASS_HORIZONTAL()],
            0,
            self::MINUTES_IN_DAY,
            2
        );
    }
}
