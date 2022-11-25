<?php
namespace SuplaBundle\Model\ChannelActionExecutor;

use SuplaBundle\Entity\ActionableSubject;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelFunctionAction;

class InterruptActionExecutor extends SingleChannelActionExecutor {
    public function getSupportedFunctions(): array {
        return [ChannelFunction::SCENE()];
    }

    public function getSupportedAction(): ChannelFunctionAction {
        return ChannelFunctionAction::INTERRUPT();
    }

    public function execute(ActionableSubject $scene, array $actionParams = []) {
        $command = $scene->buildServerActionCommand('INTERRUPT-SCENE');
        $this->suplaServer->executeCommand($command);
    }
}
