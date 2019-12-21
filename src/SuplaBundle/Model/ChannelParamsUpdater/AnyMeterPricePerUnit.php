<?php
namespace SuplaBundle\Model\ChannelParamsUpdater;

use SuplaBundle\Enums\ChannelFunction;

class AnyMeterPricePerUnit extends RangeParamsUpdater {
    const MIN_PRICE = 0;
    const MAX_PRICE = 1000;

    public function __construct() {
        parent::__construct(
            [
                ChannelFunction::ELECTRICITYMETER(),
                ChannelFunction::GASMETER(),
                ChannelFunction::WATERMETER(),
            ],
            self::MIN_PRICE * 10000,
            self::MAX_PRICE * 10000,
            2
        );
    }
}
