<?php
namespace App\Model\ChannelActionExecutor;

use App\Entity\ActionableSubject;
use App\Enums\ChannelFunctionAction;
use Assert\Assertion;

class ShutActionExecutor extends ShutPartiallyActionExecutor {
    public function getSupportedAction(): ChannelFunctionAction {
        return ChannelFunctionAction::SHUT();
    }

    public function validateAndTransformActionParamsFromApi(ActionableSubject $subject, array $actionParams): array {
        Assertion::noContent($actionParams, 'This action is not supposed to have any parameters.');
        return [];
    }

    public function execute(ActionableSubject $subject, array $actionParams = []) {
        parent::execute($subject, ['percentage' => 100, 'tilt' => 100]);
    }
}
