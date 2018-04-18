<?php
namespace SuplaApiBundle\Model\ChannelActionExecutor;

use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunctionAction;

class RevealActionExecutor extends ShutActionExecutor {
    public function getSupportedAction(): ChannelFunctionAction {
        return ChannelFunctionAction::REVEAL();
    }

    protected function getCharValue(IODeviceChannel $channel, array $actionParams = []): int {
        $percent = $actionParams['percent'] ?? 0;
        return 110 - $percent;
    }
}
