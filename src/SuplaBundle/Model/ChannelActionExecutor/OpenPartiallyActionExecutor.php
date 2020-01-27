<?php
namespace SuplaBundle\Model\ChannelActionExecutor;

use Assert\Assertion;
use SuplaBundle\Entity\HasFunction;
use SuplaBundle\Enums\ChannelFunctionAction;

class OpenPartiallyActionExecutor extends RevealActionExecutor {
    public function getSupportedAction(): ChannelFunctionAction {
        return ChannelFunctionAction::OPEN_PARTIALLY();
    }

    public function validateActionParams(HasFunction $subject, array $actionParams): array {
        Assertion::count($actionParams, 1, 'Opening percent missing.');
        return parent::validateActionParams($subject, $actionParams);
    }
}
