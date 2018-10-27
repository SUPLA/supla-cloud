<?php
namespace SuplaBundle\Model\ChannelParamsUpdater;

use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelType;

class ImpulseCounterImpulsesPerUnit extends RangeParamsUpdater {
    const MIN_COUNT = 0;
    const MAX_COUNT = 1000000;

    public function __construct() {
        parent::__construct(
            [ChannelFunction::ELECTRICITYMETER(),
                ChannelFunction::GASMETER(),
                ChannelFunction::WATERMETER()],
            self::MIN_COUNT,
            self::MAX_COUNT,
            3,
            ChannelType::IMPULSECOUNTER()
        );
    }
}
