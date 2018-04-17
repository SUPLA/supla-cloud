<?php
namespace SuplaApiBundle\Model\ChannelActionExecutor;

use Assert\Assertion;
use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunctionAction;

class ShutActionExecutor extends StopActionExecutor {
    public function getSupportedAction(): ChannelFunctionAction {
        return ChannelFunctionAction::SHUT();
    }

    public function validateActionParams(array $actionParams) {
        if ($actionParams) {
            Assertion::count($actionParams, 1, 'Too many action parameters.');
            Assertion::keyIsset($actionParams, 'percent', 'Missing required action param: percent.');
            Assertion::integer($actionParams['percent'], 'Invalid percent action param.');
            Assertion::between($actionParams['percent'], 0, 100, 'Percent should be between 0 and 100.');
        }
    }

    protected function getCharValue(IODeviceChannel $channel, array $actionParams = []) {
        $percent = $actionParams['percent'] ?? 0;
        return $percent + 10;
    }
}
