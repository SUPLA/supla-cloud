<?php
namespace App\Model\ChannelActionExecutor;

use App\Entity\ActionableSubject;
use App\Entity\Main\Schedule;
use App\Enums\ChannelFunctionAction;

class ScheduleDisableActionExecutor extends ScheduleEnableActionExecutor {
    public function getSupportedAction(): ChannelFunctionAction {
        return ChannelFunctionAction::DISABLE();
    }

    /** @param Schedule $schedule */
    public function execute(ActionableSubject $schedule, array $actionParams = []) {
        $this->getScheduleManager()->disable($schedule);
    }
}
