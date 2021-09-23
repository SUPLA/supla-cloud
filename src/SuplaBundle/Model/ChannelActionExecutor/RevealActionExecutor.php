<?php
namespace SuplaBundle\Model\ChannelActionExecutor;

use SuplaBundle\Entity\ActionableSubject;
use SuplaBundle\Enums\ChannelFunctionAction;

class RevealActionExecutor extends ShutActionExecutor {
    public function getSupportedAction(): ChannelFunctionAction {
        return ChannelFunctionAction::REVEAL();
    }

    protected function getCharValue(ActionableSubject $subject, array $actionParams = []): int {
        $percent = $actionParams['percentage'] ?? 100;
        return 110 - $percent;
    }
}
