<?php
namespace SuplaApiBundle\Model\ChannelActionExecutor;

use SuplaBundle\Entity\HasFunction;
use SuplaBundle\Enums\ChannelFunctionAction;

class TurnOffActionExecutor extends TurnOnActionExecutor {
    public function getSupportedAction(): ChannelFunctionAction {
        return ChannelFunctionAction::TURN_OFF();
    }

    protected function getCharValue(HasFunction $subject, array $actionParams = []): int {
        return 0;
    }
}
