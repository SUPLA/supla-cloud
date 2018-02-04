<?php
namespace SuplaApiBundle\ParamConverter\ChannelParamsUpdater;

use SuplaBundle\Enums\ChannelFunction;

class ControllingTheGatewayTime extends ControllingAnyLockTime {
    public function __construct() {
        parent::__construct(ChannelFunction::CONTROLLINGTHEGATEWAYLOCK(), 500, 10000);
    }
}
