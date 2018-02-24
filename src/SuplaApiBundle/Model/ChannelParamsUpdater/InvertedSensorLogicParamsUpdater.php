<?php
namespace SuplaApiBundle\Model\ChannelParamsUpdater;

use SuplaBundle\Enums\ChannelFunction;

class InvertedSensorLogicParamsUpdater extends RangeParamsUpdater {
    public function __construct() {
        parent::__construct([
            ChannelFunction::OPENINGSENSOR_WINDOW(),
            ChannelFunction::OPENINGSENSOR_GATEWAY(),
            ChannelFunction::OPENINGSENSOR_GATE(),
            ChannelFunction::OPENINGSENSOR_GARAGEDOOR(),
            ChannelFunction::OPENINGSENSOR_DOOR(),
            ChannelFunction::OPENINGSENSOR_ROLLERSHUTTER(),
            ChannelFunction::NOLIQUIDSENSOR(),
            ChannelFunction::MAILSENSOR(),
        ], 0, 1, 3);
    }
}
