<?php
namespace App\Model\ChannelActionExecutor;

use App\Entity\ActionableSubject;
use App\Enums\ChannelFunction;
use App\Enums\ChannelFunctionAction;
use Assert\Assertion;

class OpenPartiallyActionExecutor extends RevealActionExecutor {
    public function getSupportedAction(): ChannelFunctionAction {
        return ChannelFunctionAction::OPEN_PARTIALLY();
    }

    public function getSupportedFunctions(): array {
        return [
            ChannelFunction::VALVEPERCENTAGE(),
        ];
    }

    public function validateAndTransformActionParamsFromApi(ActionableSubject $subject, array $actionParams): array {
        Assertion::count($actionParams, 1, 'Opening percent missing.');
        return parent::validateAndTransformActionParamsFromApi($subject, $actionParams);
    }
}
