<?php
namespace SuplaApiBundle\ParamConverter\ChannelParamsUpdater;

use SuplaBundle\Enums\ChannelFunction;

class ControllingTheGatewayRelatedSensor extends ControllingAnyLockRelatedSensor {
    public function __construct() {
        parent::__construct(ChannelFunction::CONTROLLINGTHEGATEWAYLOCK(), ChannelFunction::OPENINGSENSOR_GATEWAY());
    }
}
