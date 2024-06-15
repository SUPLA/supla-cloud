<?php
namespace SuplaBundle\Model\ChannelActionExecutor;

use Assert\Assertion;
use SuplaBundle\Entity\ActionableSubject;
use SuplaBundle\Enums\ChannelFunctionAction;

class RevealActionExecutor extends ShutPartiallyActionExecutor {
    public function getSupportedAction(): ChannelFunctionAction {
        return ChannelFunctionAction::REVEAL();
    }

    public function validateAndTransformActionParamsFromApi(ActionableSubject $subject, array $actionParams): array {
        Assertion::noContent($actionParams, 'This action is not supposed to have any parameters.');
        return [];
    }

    public function execute(ActionableSubject $subject, array $actionParams = []) {
        parent::execute($subject, ['percentage' => 0, 'tilt' => 0]);
    }
}
