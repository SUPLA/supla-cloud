<?php
namespace SuplaBundle\Model\ChannelActionExecutor;

use SuplaBundle\Entity\ActionableSubject;
use SuplaBundle\Enums\ChannelFunctionAction;

class TurnOffActionExecutor extends TurnOnActionExecutor {
    public function getSupportedAction(): ChannelFunctionAction {
        return ChannelFunctionAction::TURN_OFF();
    }

    protected function getCharValue(ActionableSubject $subject, array $actionParams = []): int {
        return 0;
    }
}
