<?php

namespace App\Message\Common;

use App\Entity\Main\Schedule;
use App\Message\CommonMessage;
use App\Message\UserOptOutNotifications;

readonly class ScheduleDisabledDueToFailuresNotification extends CommonMessage {
    public function __construct(Schedule $schedule) {
        parent::__construct(
            $schedule->getUser(),
            UserOptOutNotifications::SCHEDULE_DISABLED_DUE_TO_FAILED_EXECUTIONS,
            [
                'schedule' => [
                    'id' => $schedule->getId(),
                    'caption' => $schedule->getCaption(),
                ],
            ],
        );
    }
}
