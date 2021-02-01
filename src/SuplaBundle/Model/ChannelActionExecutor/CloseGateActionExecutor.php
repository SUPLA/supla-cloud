<?php
namespace SuplaBundle\Model\ChannelActionExecutor;

use Assert\Assertion;
use SuplaBundle\Entity\HasFunction;
use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelFunctionAction;

class CloseGateActionExecutor extends SingleChannelActionExecutor {
    public function execute(HasFunction $subject, array $actionParams = []) {
        $command = $subject->buildServerSetCommand('ACTION-CLOSE', $this->assignCommonParams([], $actionParams));
        $command = str_replace('SET-ACTION-CLOSE-VALUE:', 'ACTION-CLOSE:', $command);
        $this->suplaServer->executeSetCommand($command);
    }

    public function getSupportedFunctions(): array {
        return [
            ChannelFunction::CONTROLLINGTHEGARAGEDOOR(),
            ChannelFunction::CONTROLLINGTHEGATE(),
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
