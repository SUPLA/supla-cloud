<?php
namespace SuplaBundle\Model\ChannelActionExecutor;

use Assert\Assertion;
use SuplaBundle\Entity\HasFunction;
use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelFunctionAction;

class CloseActionExecutor extends SetCharValueActionExecutor {
    protected function getCharValue(HasFunction $subject, array $actionParams = []): int {
        if ($this->isGateSubject($subject)) {
            return 3;
        } else {
            return 0;
        }
    }

    private function isGateSubject(HasFunction $subject): bool {
        return in_array(
            $subject->getFunction()->getId(),
            [ChannelFunction::CONTROLLINGTHEGATE, ChannelFunction::CONTROLLINGTHEGARAGEDOOR]
        );
    }

    public function getSupportedFunctions(): array {
        return [
//            ChannelFunction::CONTROLLINGTHEGARAGEDOOR(),
//            ChannelFunction::CONTROLLINGTHEGATE(),
            ChannelFunction::VALVEOPENCLOSE(),
        ];
    }

    public function getSupportedAction(): ChannelFunctionAction {
        return ChannelFunctionAction::CLOSE();
    }

    public function validateActionParams(HasFunction $subject, array $actionParams): array {
        Assertion::true(
            $subject instanceof IODeviceChannel,
            "Cannot execute the requested action CLOSE on channel group."
        );
        return parent::validateActionParams($subject, $actionParams);
    }
}
