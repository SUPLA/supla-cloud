<?php
namespace SuplaBundle\Model\ChannelParamsUpdater;

use SuplaBundle\Enums\ChannelFunction;

class ControllingTheStaircaseTimer extends RangeParamsUpdater {
    const MAX_TIME_IN_SECONDS = 7200;

    public function __construct() {
        parent::__construct(ChannelFunction::STAIRCASETIMER(), 5, self::MAX_TIME_IN_SECONDS * 10);
    }
}
