<?php
namespace SuplaBundle\Model\ChannelParamsUpdater;

use SuplaBundle\Enums\ChannelFunction;

class AnyMeterUnitPrice extends RangeParamsUpdater {
    const MIN_PRICE = 0;
    const MAX_PRICE = 1000;

    public function __construct() {
        parent::__construct(
            [ChannelFunction::ELECTRICITYMETER(),
                ChannelFunction::GASMETER(),
                ChannelFunction::WATERMETER()],
            self::MIN_PRICE * 100,
            self::MAX_PRICE * 100,
            2
        );
    }
}
