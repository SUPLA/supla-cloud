<?php
namespace SuplaBundle\Model\ChannelActionExecutor;

use SuplaBundle\Entity\HasFunction;
use SuplaBundle\Enums\ChannelFunctionAction;

class RevealActionExecutor extends ShutActionExecutor {
    public function getSupportedAction(): ChannelFunctionAction {
        return ChannelFunctionAction::REVEAL();
    }

    protected function getCharValue(HasFunction $subject, array $actionParams = []): int {
        $percent = $actionParams['percentage'] ?? 0;
        return 110 - $percent;
    }
}
