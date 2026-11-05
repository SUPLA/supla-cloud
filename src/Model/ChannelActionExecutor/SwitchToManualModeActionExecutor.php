<?php
namespace App\Model\ChannelActionExecutor;

use App\Entity\ActionableSubject;
use App\Enums\ChannelFunctionAction;

class SwitchToManualModeActionExecutor extends SwitchToProgramModeActionExecutor {
    public function getSupportedAction(): ChannelFunctionAction {
        return ChannelFunctionAction::SWITCH_TO_MANUAL_MODE();
    }

    public function execute(ActionableSubject $subject, array $actionParams = []) {
        $command = $subject->buildServerActionCommand('ACTION-SWITCH-TO-MANUAL-MODE');
        $this->suplaServer->executeCommand($command);
    }
}
