<?php
namespace SuplaApiBundle\Model\ChannelActionExecutor;

use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunctionAction;

class TurnOffActionExecutor extends TurnOnActionExecutor {
    public function getSupportedAction(): ChannelFunctionAction {
        return ChannelFunctionAction::TURN_OFF();
    }

    protected function getCharValue(IODeviceChannel $channel, array $actionParams = []) {
        return 0;
    }
}
