<?php
namespace SuplaBundle\Model\ChannelActionExecutor;

use SuplaBundle\Entity\ActionableSubject;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelFunctionAction;

class TurnOnActionExecutor extends SingleChannelActionExecutor {
    public function getSupportedFunctions(): array {
        return [
            ChannelFunction::POWERSWITCH(),
            ChannelFunction::LIGHTSWITCH(),
            ChannelFunction::STAIRCASETIMER(),
            ChannelFunction::DIMMER(),
            ChannelFunction::RGBLIGHTING(),
            ChannelFunction::DIMMERANDRGBLIGHTING(),
            ChannelFunction::THERMOSTAT(),
            ChannelFunction::THERMOSTATHEATPOLHOMEPLUS(),
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
