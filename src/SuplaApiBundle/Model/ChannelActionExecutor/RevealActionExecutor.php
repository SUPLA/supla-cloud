<?php
namespace SuplaApiBundle\Model\ChannelActionExecutor;

use Assert\Assertion;
use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelFunctionAction;

class RevealActionExecutor extends ShutActionExecutor {
    public function getSupportedAction(): ChannelFunctionAction {
        return ChannelFunctionAction::REVEAL();
    }

    protected function getCharValue(IODeviceChannel $channel, array $actionParams = []) {
        $percent = $actionParams['percent'] ?? 0;
        return 110 - $percent;
    }
}
