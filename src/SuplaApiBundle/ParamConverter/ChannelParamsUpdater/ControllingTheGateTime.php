<?php
namespace SuplaApiBundle\ParamConverter\ChannelParamsUpdater;

use SuplaBundle\Enums\ChannelFunction;

class ControllingTheGateTime extends ControllingAnyLockTime {
    public function __construct() {
        parent::__construct(ChannelFunction::CONTROLLINGTHEGATE(), 500, 2000);
    }
}
