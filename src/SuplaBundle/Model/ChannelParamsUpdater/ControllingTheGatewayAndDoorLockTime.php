<?php
namespace SuplaBundle\Model\ChannelParamsUpdater;

use SuplaBundle\Enums\ChannelFunction;

class ControllingTheGatewayAndDoorLockTime extends RangeParamsUpdater {
    const MIN_TIME_IN_SECONDS = 0.5;
    const MAX_TIME_IN_SECONDS = 10;

    public function __construct() {
        parent::__construct(
            [ChannelFunction::CONTROLLINGTHEDOORLOCK(), ChannelFunction::CONTROLLINGTHEGATEWAYLOCK()],
            self::MIN_TIME_IN_SECONDS * 1000,
            self::MAX_TIME_IN_SECONDS * 1000
        );
    }
}
