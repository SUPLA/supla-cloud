<?php
namespace SuplaBundle\Model\ChannelActionExecutor;

use Assert\Assertion;
use SuplaBundle\Entity\HasFunction;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelFunctionAction;
use SuplaBundle\Supla\SuplaServerAware;

abstract class SingleChannelActionExecutor {
    use SuplaServerAware;

    /** @return ChannelFunction[] */
    abstract public function getSupportedFunctions(): array;

    abstract public function getSupportedAction(): ChannelFunctionAction;

    abstract public function execute(HasFunction $subject, array $actionParams = []);

    public function validateActionParams(HasFunction $subject, array $actionParams): array {
        Assertion::noContent($actionParams, 'This action is not supposed to have any parameters.');
        return $actionParams;
    }
}
