<?php
namespace SuplaBundle\Model\ChannelActionExecutor;

use Assert\Assertion;
use SuplaBundle\Entity\ActionableSubject;
use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelFunctionAction;

class CloseActionExecutor extends SetCharValueActionExecutor {
    public function getSupportedFunctions(): array {
        return [
            ChannelFunction::VALVEOPENCLOSE(),
        ];
    }

    public function getSupportedAction(): ChannelFunctionAction {
        return ChannelFunctionAction::CLOSE();
    }

    public function validateAndTransformActionParamsFromApi(ActionableSubject $subject, array $actionParams): array {
        if ($subject->getFunction()->getId() !== ChannelFunction::VALVEOPENCLOSE) {
            Assertion::true(
                $subject instanceof IODeviceChannel,
                "Cannot execute the requested action CLOSE on channel group."
            );
        }
        return parent::validateAndTransformActionParamsFromApi($subject, $actionParams);
    }

    protected function getCharValue(ActionableSubject $subject, array $actionParams = []): int {
        return 0;
    }
}
