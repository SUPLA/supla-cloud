<?php
namespace SuplaApiBundle\ParamConverter\ChannelParamsUpdater;

use SuplaBundle\Enums\ChannelFunction;

class ControllingTheGarageDoorTime extends ControllingAnyLockTime {
    public function __construct() {
        parent::__construct(ChannelFunction::CONTROLLINGTHEGARAGEDOOR(), 500, 2000);
    }
}
