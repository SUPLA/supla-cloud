<?php
namespace SuplaApiBundle\Model\ChannelParamsUpdater;

use SuplaBundle\Enums\ChannelFunction;

class ControllingTheGateAndGarageDoorTime extends RangeParamsUpdater {
    const MIN_TIME_IN_SECONDS = 0.5;
    const MAX_TIME_IN_SECONDS = 2;

    public function __construct() {
        parent::__construct(
            [ChannelFunction::CONTROLLINGTHEGARAGEDOOR(), ChannelFunction::CONTROLLINGTHEGATE()],
            self::MIN_TIME_IN_SECONDS * 1000,
            self::MAX_TIME_IN_SECONDS * 1000
        );
    }
}
