<?php
namespace SuplaApiBundle\Model\ChannelActionExecutor;

use Assert\Assertion;
use SuplaBundle\Entity\HasFunction;
use SuplaBundle\Enums\ChannelFunctionAction;

class RevealPartiallyActionExecutor extends ShutActionExecutor {
    public function getSupportedAction(): ChannelFunctionAction {
        return ChannelFunctionAction::REVEAL_PARTIALLY();
    }

    public function validateActionParams(HasFunction $subject, array $actionParams): array {
        Assertion::count($actionParams, 1, 'Reveal percent missing.');
        return parent::validateActionParams($subject, $actionParams);
    }
}
