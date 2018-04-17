<?php
namespace SuplaApiBundle\Model\ChannelActionExecutor;

use Assert\Assertion;
use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunctionAction;

class ChannelActionExecutor {
    /** @var SingleChannelActionExecutor[] */
    private $actionExecutors = [];

    /** @param SingleChannelActionExecutor[] $actionExecutors */
    public function __construct($actionExecutors) {
        foreach ($actionExecutors as $actionExecutor) {
            $this->actionExecutors[$actionExecutor->getSupportedAction()->getName()] = $actionExecutor;
        }
    }

    public function executeAction(IODeviceChannel $channel, ChannelFunctionAction $action, array $actionParams = []) {
        Assertion::keyIsset($this->actionExecutors, $action->getName(), 'Cannot execute requested action through API.');
        $executor = $this->actionExecutors[$action->getName()];
        Assertion::inArray($channel->getFunction(), $executor->getSupportedFunctions(), 'Cannot execute requested action on this channel.');
        $executor->validateActionParams($actionParams);
        $executor->execute($channel, $actionParams);
    }
}
