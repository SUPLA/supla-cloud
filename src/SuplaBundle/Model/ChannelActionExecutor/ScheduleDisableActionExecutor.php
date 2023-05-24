<?php
namespace SuplaBundle\Model\ChannelActionExecutor;

use SuplaBundle\Entity\ActionableSubject;
use SuplaBundle\Entity\Main\Schedule;
use SuplaBundle\Enums\ChannelFunctionAction;

class ScheduleDisableActionExecutor extends ScheduleEnableActionExecutor {
    public function getSupportedAction(): ChannelFunctionAction {
        return ChannelFunctionAction::DISABLE();
    }

    /** @param Schedule $schedule */
    public function execute(ActionableSubject $schedule, array $actionParams = []) {
        $this->getScheduleManager()->disable($schedule);
    }
}
