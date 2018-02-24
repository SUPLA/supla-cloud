<?php
namespace SuplaApiBundle\Model\ChannelParamsUpdater;

use SuplaBundle\Enums\ChannelFunction;

class HumidityAdjustmentParamsUpdater extends RangeParamsUpdater {
    const MINIMUM_ADJUSTMENT_IN_PERCENT = -10;
    const MAXIMUM_ADJUSTMENT_IN_PERCENT = 10;

    public function __construct() {
        parent::__construct(
            [ChannelFunction::HUMIDITY(), ChannelFunction::HUMIDITYANDTEMPERATURE()],
            self::MINIMUM_ADJUSTMENT_IN_PERCENT * 100,
            self::MAXIMUM_ADJUSTMENT_IN_PERCENT * 100,
            3
        );
    }
}
