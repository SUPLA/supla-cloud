<?php
namespace SuplaBundle\Model\ChannelActionExecutor;

use Assert\Assertion;
use SuplaBundle\Entity\ActionableSubject;
use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelFunctionAction;

class OpenGateActionExecutor extends SingleChannelActionExecutor {
    public function execute(ActionableSubject $subject, array $actionParams = []) {
        $command = $subject->buildServerActionCommand('ACTION-OPEN', $this->assignCommonParams([], $actionParams));
        $this->suplaServer->executeCommand($command);
    }

    public function getSupportedFunctions(): array {
        return [
            ChannelFunction::CONTROLLINGTHEGARAGEDOOR(),
            ChannelFunction::CONTROLLINGTHEGATE(),
        ];
    }

    public function getSupportedAction(): ChannelFunctionAction {
        return ChannelFunctionAction::OPEN();
    }

    public function validateActionParams(ActionableSubject $subject, array $actionParams): array {
        Assertion::true(
            $subject instanceof IODeviceChannel,
            "Cannot execute the requested action OPEN on channel group."
        );
        return parent::validateActionParams($subject, $actionParams);
    }
}
