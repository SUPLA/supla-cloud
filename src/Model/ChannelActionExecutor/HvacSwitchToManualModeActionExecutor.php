<?php
namespace App\Model\ChannelActionExecutor;

use App\Entity\ActionableSubject;
use App\Enums\ChannelFunctionAction;

class HvacSwitchToManualModeActionExecutor extends HvacSwitchToProgramModeActionExecutor {
    public function getSupportedAction(): ChannelFunctionAction {
        return ChannelFunctionAction::HVAC_SWITCH_TO_MANUAL_MODE();
    }

    public function execute(ActionableSubject $subject, array $actionParams = []) {
        $command = $subject->buildServerActionCommand('ACTION-HVAC-SWITCH-TO-MANUAL-MODE');
        $this->suplaServer->executeCommand($command);
    }
}
