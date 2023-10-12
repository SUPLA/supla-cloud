<?php
namespace SuplaBundle\Model\ChannelActionExecutor;

use SuplaBundle\Entity\ActionableSubject;
use SuplaBundle\Enums\ChannelFunctionAction;

class TurnOffActionExecutor extends TurnOnActionExecutor {
    public function getSupportedAction(): ChannelFunctionAction {
        return ChannelFunctionAction::TURN_OFF();
    }

    public function execute(ActionableSubject $subject, array $actionParams = []) {
        $command = $subject->buildServerActionCommand('ACTION-TURN-OFF');
        $this->suplaServer->executeCommand($command);
    }
}
