<?php
namespace SuplaBundle\Model\ChannelActionExecutor;

use Assert\Assertion;
use SuplaBundle\Entity\Main\HasFunction;
use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelFunctionAction;

class OpenGateActionExecutor extends SingleChannelActionExecutor {
    public function execute(HasFunction $subject, array $actionParams = []) {
        $command = $subject->buildServerSetCommand('ACTION-OPEN', $this->assignCommonParams([], $actionParams));
        $command = str_replace('SET-ACTION-OPEN-VALUE:', 'ACTION-OPEN:', $command);
        $this->suplaServer->executeSetCommand($command);
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

    public function validateActionParams(HasFunction $subject, array $actionParams): array {
        Assertion::true(
            $subject instanceof IODeviceChannel,
            "Cannot execute the requested action OPEN on channel group."
        );
        return parent::validateActionParams($subject, $actionParams);
    }
}
