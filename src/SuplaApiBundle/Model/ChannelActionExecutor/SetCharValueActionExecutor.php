<?php
namespace SuplaApiBundle\Model\ChannelActionExecutor;

use SuplaBundle\Entity\IODeviceChannel;

abstract class SetCharValueActionExecutor extends SingleChannelActionExecutor {
    protected function getCharValue(IODeviceChannel $channel, array $actionParams = []): int {
        return 1;
    }

    public function execute(IODeviceChannel $channel, array $actionParams = []) {
        $this->suplaServer->setCharValue($channel, $this->getCharValue($channel, $actionParams));
    }
}
