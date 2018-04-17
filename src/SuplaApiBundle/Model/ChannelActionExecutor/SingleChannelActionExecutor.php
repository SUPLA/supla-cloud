<?php
namespace SuplaApiBundle\Model\ChannelActionExecutor;

use Assert\Assertion;
use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelFunctionAction;
use SuplaBundle\Supla\SuplaServerAware;

abstract class SingleChannelActionExecutor {
    use SuplaServerAware;

    /** @return ChannelFunction[] */
    abstract public function getSupportedFunctions(): array;

    abstract public function getSupportedAction(): ChannelFunctionAction;

    abstract public function execute(IODeviceChannel $channel, array $actionParams = []);

    public function validateActionParams(array $actionParams) {
        Assertion::noContent($actionParams, 'This action is not supposed to have any parameters.');
    }
}
