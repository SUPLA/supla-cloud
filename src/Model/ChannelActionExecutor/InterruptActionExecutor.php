<?php
namespace App\Model\ChannelActionExecutor;

use App\Entity\ActionableSubject;
use App\Enums\ChannelFunction;
use App\Enums\ChannelFunctionAction;

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
