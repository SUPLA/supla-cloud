<?php
namespace SuplaBundle\Model\ChannelActionExecutor;

use SuplaBundle\Entity\ActionableSubject;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelFunctionAction;

class HvacSwitchToProgramModeActionExecutor extends SingleChannelActionExecutor {
    public function getSupportedFunctions(): array {
        return [
            ChannelFunction::HVAC_THERMOSTAT(),
            ChannelFunction::HVAC_THERMOSTAT_HEAT_COOL(),
            ChannelFunction::HVAC_THERMOSTAT_DIFFERENTIAL(),
            ChannelFunction::HVAC_DOMESTIC_HOT_WATER(),
            ChannelFunction::THERMOSTATHEATPOLHOMEPLUS(),
        ];
    }

    public function getSupportedAction(): ChannelFunctionAction {
        return ChannelFunctionAction::HVAC_SWITCH_TO_PROGRAM_MODE();
    }

    public function execute(ActionableSubject $subject, array $actionParams = []) {
        $command = $subject->buildServerActionCommand('ACTION-HVAC-SWITCH-TO-PROGRAM-MODE');
        $this->suplaServer->executeCommand($command);
    }
}
