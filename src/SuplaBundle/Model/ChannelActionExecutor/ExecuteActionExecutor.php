<?php
namespace SuplaBundle\Model\ChannelActionExecutor;

use SuplaBundle\Entity\ActionableSubject;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelFunctionAction;

class ExecuteActionExecutor extends SingleChannelActionExecutor {
    public function getSupportedFunctions(): array {
        return [ChannelFunction::SCENE()];
    }

    public function getSupportedAction(): ChannelFunctionAction {
        return ChannelFunctionAction::EXECUTE();
    }

    public function execute(ActionableSubject $scene, array $actionParams = []) {
        $command = $scene->buildServerActionCommand('EXECUTE-SCENE', $this->assignCommonParams([], $actionParams));
        $this->suplaServer->executeCommand($command);
    }
}
