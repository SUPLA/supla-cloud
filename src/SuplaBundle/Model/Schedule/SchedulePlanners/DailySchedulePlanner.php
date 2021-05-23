<?php
/*
 Copyright (C) AC SOFTWARE SP. Z O.O.
 
 This program is free software; you can redistribute it and/or
 modify it under the terms of the GNU General Public License
 as published by the Free Software Foundation; either version 2
 of the License, or (at your option) any later version.
 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.
 You should have received a copy of the GNU General Public License
 along with this program; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
 */

namespace SuplaBundle\Model\Schedule\SchedulePlanners;

use DateTime;
use SuplaBundle\Entity\Schedule;
use SuplaBundle\Entity\ScheduledExecution;
use SuplaBundle\Enums\ScheduleMode;

class DailySchedulePlanner implements SchedulePlanner {
    /** @var CompositeSchedulePlanner */
    private $compositePlanner;

    public function calculateNextScheduleExecution(Schedule $schedule, DateTime $currentDate) {
        /** @var ScheduledExecution $closestExecution */
        $closestExecution = null;
        foreach ($schedule->getConfig() as $executionDef) {
            $tempSchedule = new Schedule($schedule->getUser(), [
                'mode' => ScheduleMode::ONCE,
                'timeExpression' => $executionDef['crontab'],
                'actionId' => $executionDef['action']['id'],
                'actionParam' => $executionDef['action']['param'] ?? [],
            ]);
            $thisExecution = $this->compositePlanner->calculateNextScheduleExecution($tempSchedule, $currentDate);
            if (!$closestExecution || $closestExecution->getPlannedTimestamp() > $thisExecution->getPlannedTimestamp()) {
                $closestExecution = $thisExecution;
            }
        }
        return $closestExecution;
    }

    public function canCalculateFor(Schedule $schedule) {
        return $schedule->getMode()->getKey() === ScheduleMode::ONOFF;
    }
}
