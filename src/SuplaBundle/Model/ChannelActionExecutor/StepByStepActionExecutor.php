<?php
namespace SuplaBundle\Model\ChannelActionExecutor;

use SuplaBundle\Entity\ActionableSubject;
use SuplaBundle\Enums\ChannelFunctionAction;

class StepByStepActionExecutor extends UpOrStopActionExecutor {
    public function execute(ActionableSubject $subject, array $actionParams = []) {
        $command = $subject->buildServerActionCommand('ACTION-SBS');
        $this->suplaServer->executeCommand($command);
    }

    public function getSupportedAction(): ChannelFunctionAction {
        return ChannelFunctionAction::STEP_BY_STEP();
    }
}
