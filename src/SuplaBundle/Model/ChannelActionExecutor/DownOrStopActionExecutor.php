<?php
namespace SuplaBundle\Model\ChannelActionExecutor;

use SuplaBundle\Entity\ActionableSubject;
use SuplaBundle\Enums\ChannelFunctionAction;

class DownOrStopActionExecutor extends UpOrStopActionExecutor {
    public function execute(ActionableSubject $subject, array $actionParams = []) {
        $command = $subject->buildServerActionCommand('ACTION-DOWN-OR-STOP');
        $this->suplaServer->executeCommand($command);
    }

    public function getSupportedAction(): ChannelFunctionAction {
        return ChannelFunctionAction::DOWN_OR_STOP();
    }
}
