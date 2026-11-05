<?php
namespace App\Model\ChannelActionExecutor;

use App\Entity\ActionableSubject;
use App\Enums\ChannelFunction;
use App\Enums\ChannelFunctionAction;

class SwitchToProgramModeActionExecutor extends SingleChannelActionExecutor {
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
        return ChannelFunctionAction::SWITCH_TO_PROGRAM_MODE();
    }

    public function execute(ActionableSubject $subject, array $actionParams = []) {
        $command = $subject->buildServerActionCommand('ACTION-SWITCH-TO-PROGRAM-MODE');
        $this->suplaServer->executeCommand($command);
    }
}
