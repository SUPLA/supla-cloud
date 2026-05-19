<?php
namespace App\Model\ChannelActionExecutor;

use App\Entity\ActionableSubject;
use App\Entity\Main\Schedule;
use App\Enums\ChannelFunction;
use App\Enums\ChannelFunctionAction;
use App\Model\Schedule\ScheduleManager;

class ScheduleEnableActionExecutor extends SingleChannelActionExecutor {
    /** @var ScheduleManager */
    protected $scheduleManager;

    /** @required */
    public function setScheduleManager(ScheduleManager $scheduleManager): void {
        $this->scheduleManager = $scheduleManager;
    }

    public function getSupportedFunctions(): array {
        return [ChannelFunction::SCHEDULE()];
    }

    public function getSupportedAction(): ChannelFunctionAction {
        return ChannelFunctionAction::ENABLE();
    }

    /** @param Schedule $schedule */
    public function execute(ActionableSubject $schedule, array $actionParams = []) {
        $this->getScheduleManager()->enable($schedule);
    }

    protected function getScheduleManager(): ScheduleManager {
        return $this->scheduleManager;
    }
}
