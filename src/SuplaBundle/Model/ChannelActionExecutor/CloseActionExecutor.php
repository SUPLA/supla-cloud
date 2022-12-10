<?php
namespace SuplaBundle\Model\ChannelActionExecutor;

use Assert\Assertion;
use SuplaBundle\Entity\ActionableSubject;
use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelFunctionAction;

class CloseActionExecutor extends TurnOffActionExecutor {
    public function getSupportedFunctions(): array {
        return [
            ChannelFunction::VALVEOPENCLOSE(),
        ];
    }

    public function getSupportedAction(): ChannelFunctionAction {
        return ChannelFunctionAction::CLOSE();
    }

    public function validateActionParams(ActionableSubject $subject, array $actionParams): array {
        Assertion::true(
            $subject instanceof IODeviceChannel,
            "Cannot execute the requested action CLOSE on channel group."
        );
        return parent::validateActionParams($subject, $actionParams);
    }
}
