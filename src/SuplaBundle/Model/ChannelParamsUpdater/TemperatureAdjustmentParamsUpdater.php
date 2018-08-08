<?php
namespace SuplaBundle\Model\ChannelParamsUpdater;

use SuplaBundle\Enums\ChannelFunction;

class TemperatureAdjustmentParamsUpdater extends RangeParamsUpdater {
    const MINIMUM_ADJUSTMENT_IN_CELSIUS = -10;
    const MAXIMUM_ADJUSTMENT_IN_CELSIUS = 10;

    public function __construct() {
        parent::__construct(
            [ChannelFunction::THERMOMETER(), ChannelFunction::HUMIDITYANDTEMPERATURE()],
            self::MINIMUM_ADJUSTMENT_IN_CELSIUS * 100,
            self::MAXIMUM_ADJUSTMENT_IN_CELSIUS * 100,
            2
        );
    }
}
