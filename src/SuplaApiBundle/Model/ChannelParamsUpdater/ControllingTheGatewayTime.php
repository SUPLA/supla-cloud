<?php
namespace SuplaApiBundle\Model\ChannelParamsUpdater;

use SuplaBundle\Enums\ChannelFunction;

class ControllingTheGatewayTime extends RangeParamsUpdater {
    public function __construct() {
        parent::__construct(ChannelFunction::CONTROLLINGTHEGATEWAYLOCK(), 500, 10000);
    }
}
