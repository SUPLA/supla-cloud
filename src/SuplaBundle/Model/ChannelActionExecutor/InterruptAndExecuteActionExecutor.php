<?php
namespace SuplaBundle\Model\ChannelActionExecutor;

use SuplaBundle\Entity\ActionableSubject;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelFunctionAction;

class InterruptAndExecuteActionExecutor extends SingleChannelActionExecutor {
    public function getSupportedFunctions(): array {
        return [ChannelFunction::SCENE()];
    }

    public function getSupportedAction(): ChannelFunctionAction {
        return ChannelFunctionAction::INTERRUPT_AND_EXECUTE();
    }

    public function execute(ActionableSubject $scene, array $actionParams = []) {
        $command = $scene->buildServerActionCommand('INTERRUPT-AND-EXECUTE-SCENE', $this->assignCommonParams([], $actionParams));
        $this->suplaServer->executeCommand($command);
    }
}
