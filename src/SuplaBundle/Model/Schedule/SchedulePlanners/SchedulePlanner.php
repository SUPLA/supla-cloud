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
use RuntimeException;
use SuplaBundle\Entity\Schedule;
use SuplaBundle\Entity\ScheduledExecution;

interface SchedulePlanner {
    /**
     * Calculates next run date of a given schedule from the specified current date.
     *
     * @param Schedule $schedule the schedule to calculate the next run date for
     * @param DateTime $currentDate the current date
     * @return ScheduledExecution the next execution
     * @throws RuntimeException if the next run date could not be calculated
     */
    public function calculateNextScheduleExecution(Schedule $schedule, DateTime $currentDate);

    /**
     * Checks if it can calculate the next run date for given schedule.
     *
     * @param Schedule $schedule the schedule to calculate the next run date for
     * @return boolean true if the calculation is possible, false otherwise
     */
    public function canCalculateFor(Schedule $schedule);
}
