<?php
namespace App\Model\ChannelActionExecutor;

use App\Entity\ActionableSubject;
use App\Enums\ChannelFunction;
use App\Enums\ChannelFunctionAction;

class TurnOnActionExecutor extends SingleChannelActionExecutor {
    public function getSupportedFunctions(): array {
        return [
            ChannelFunction::POWERSWITCH(),
            ChannelFunction::LIGHTSWITCH(),
            ChannelFunction::STAIRCASETIMER(),
            ChannelFunction::DIMMER(),
            ChannelFunction::DIMMER_CCT(),
            ChannelFunction::RGBLIGHTING(),
            ChannelFunction::DIMMER_CCT_AND_RGB(),
            ChannelFunction::DIMMERANDRGBLIGHTING(),
            ChannelFunction::THERMOSTAT(),
            ChannelFunction::THERMOSTATHEATPOLHOMEPLUS(),
            ChannelFunction::HVAC_THERMOSTAT(),
            ChannelFunction::HVAC_THERMOSTAT_HEAT_COOL(),
            ChannelFunction::HVAC_THERMOSTAT_DIFFERENTIAL(),
            ChannelFunction::HVAC_DOMESTIC_HOT_WATER(),
        ];
    }

    public function execute(ActionableSubject $subject, array $actionParams = []) {
        $command = $subject->buildServerActionCommand('ACTION-TURN-ON');
        $this->suplaServer->executeCommand($command);
    }

    public function getSupportedAction(): ChannelFunctionAction {
        return ChannelFunctionAction::TURN_ON();
    }
}
