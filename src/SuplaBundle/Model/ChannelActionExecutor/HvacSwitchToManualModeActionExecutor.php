<?php
namespace SuplaBundle\Model\ChannelActionExecutor;

use SuplaBundle\Entity\ActionableSubject;
use SuplaBundle\Enums\ChannelFunctionAction;

class HvacSwitchToManualModeActionExecutor extends HvacSwitchToProgramModeActionExecutor {
    public function getSupportedAction(): ChannelFunctionAction {
        return ChannelFunctionAction::HVAC_SWITCH_TO_MANUAL_MODE();
    }

    public function execute(ActionableSubject $subject, array $actionParams = []) {
        $command = $subject->buildServerActionCommand('ACTION-HVAC-SWITCH-TO-MANUAL-MODE');
        $this->suplaServer->executeCommand($command);
    }
}
