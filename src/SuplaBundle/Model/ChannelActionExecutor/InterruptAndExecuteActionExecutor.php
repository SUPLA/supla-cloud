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
        $this->suplaServer->executeScene($scene, 'INTERRUPT-AND-EXECUTE-SCENE');
    }
}
