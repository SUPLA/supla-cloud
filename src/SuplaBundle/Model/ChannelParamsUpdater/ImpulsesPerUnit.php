<?php
namespace SuplaBundle\Model\ChannelParamsUpdater;

use SuplaBundle\Enums\ChannelFunction;

class ImpulsesPerUnit extends RangeParamsUpdater {

    public function __construct() {
        parent::__construct(
            [ChannelFunction::ELECTRICITYMETER(),
                ChannelFunction::GASMETER(),
                ChannelFunction::WATERMETER(),
                ChannelFunction::HEATMETER()],
            0,
            1000000,
            3
        );
    }
}
