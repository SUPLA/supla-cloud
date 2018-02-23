<?php
namespace SuplaApiBundle\ParamConverter\ChannelParamsUpdater;

use SuplaBundle\Enums\ChannelFunction;

class ControllingTheStaircaseTimer extends RangeParamsUpdater {
    public function __construct() {
        parent::__construct(ChannelFunction::STAIRCASETIMER(), 0, 3600000);
    }
}
