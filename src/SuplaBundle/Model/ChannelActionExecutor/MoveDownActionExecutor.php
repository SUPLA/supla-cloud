<?php
namespace SuplaBundle\Model\ChannelActionExecutor;

use SuplaBundle\Entity\ActionableSubject;
use SuplaBundle\Enums\ChannelFunctionAction;

class MoveDownActionExecutor extends MoveUpActionExecutor {
    public function execute(ActionableSubject $subject, array $actionParams = []) {

        $command = $subject->buildServerActionCommand('ACTION-MOVE-DOWN', $this->assignCommonParams([], $actionParams));
        $this->suplaServer->executeCommand($command);
    }

    public function getSupportedAction(): ChannelFunctionAction {
        return ChannelFunctionAction::MOVE_DOWN();
    }
}
