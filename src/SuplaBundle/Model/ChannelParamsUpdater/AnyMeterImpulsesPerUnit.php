<?php
namespace SuplaBundle\Model\ChannelParamsUpdater;

use SuplaBundle\Enums\ChannelFunction;

class AnyMeterImpulsesPerUnit extends RangeParamsUpdater {

    public function __construct() {
        parent::__construct(
            [ChannelFunction::ELECTRICITYMETER(),
                ChannelFunction::GASMETER(),
                ChannelFunction::WATERMETER()],
            0,
            1000000,
            3
        );
    }
}
