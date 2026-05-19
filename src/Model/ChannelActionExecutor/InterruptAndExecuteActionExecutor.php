<?php
namespace App\Model\ChannelActionExecutor;

use App\Entity\ActionableSubject;
use App\Enums\ChannelFunction;
use App\Enums\ChannelFunctionAction;

class InterruptAndExecuteActionExecutor extends SingleChannelActionExecutor {
    public function getSupportedFunctions(): array {
        return [ChannelFunction::SCENE()];
    }

    public function getSupportedAction(): ChannelFunctionAction {
        return ChannelFunctionAction::INTERRUPT_AND_EXECUTE();
    }

    public function execute(ActionableSubject $scene, array $actionParams = []) {
        $this->suplaServer->executeScene($scene, 'INTERRUPT-AND-EXECUTE-SCENE');
    }
}
