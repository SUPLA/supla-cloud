<?php
namespace SuplaBundle\Model\ChannelActionExecutor;

use Assert\Assertion;
use SuplaBundle\Entity\ActionableSubject;
use SuplaBundle\Enums\ChannelFunctionAction;

class RevealPartiallyActionExecutor extends RevealActionExecutor {
    public function getSupportedAction(): ChannelFunctionAction {
        return ChannelFunctionAction::REVEAL_PARTIALLY();
    }

    public function validateAndTransformActionParamsFromApi(ActionableSubject $subject, array $actionParams): array {
        Assertion::count($actionParams, 1, 'Reveal percent missing.');
        return parent::validateAndTransformActionParamsFromApi($subject, $actionParams);
    }
}
