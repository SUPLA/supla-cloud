<?php
namespace SuplaBundle\Model\ChannelActionExecutor;

use Assert\Assertion;
use SuplaBundle\Entity\ActionableSubject;
use SuplaBundle\Enums\ChannelFunctionAction;

class ShutPartiallyActionExecutor extends ShutActionExecutor {
    public function getSupportedAction(): ChannelFunctionAction {
        return ChannelFunctionAction::SHUT_PARTIALLY();
    }

    public function validateActionParams(ActionableSubject $subject, array $actionParams): array {
        Assertion::count($actionParams, 1, 'Reveal percent missing.');
        return parent::validateActionParams($subject, $actionParams);
    }
}
