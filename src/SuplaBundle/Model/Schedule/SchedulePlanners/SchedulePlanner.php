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

abstract class SchedulePlanner {
    /**
     * Calculates next run date of a given schedule from the specified current date.
     *
     * @param string $crontab the crontab expression to calculate the next run date for
     * @param DateTime $currentDate the current date
     * @return \SuplaBundle\Entity\Main\ScheduledExecution the next execution
     * @throws RuntimeException if the next run date could not be calculated
     */
    abstract public function calculateNextScheduleExecution(string $crontab, DateTime $currentDate): DateTime;

    /**
     * Checks if it can calculate the next run date for given crontab..
     *
     * @param string $crontab
     * @return boolean true if the calculation is possible, false otherwise
     */
    abstract public function canCalculateFor(string $crontab): bool;

    /**
     * Checks whether the crontab is valid for this schedule planner.
     *
     * @param string $crontab
     * @throws RuntimeException if the schedule is invalid
     */
    public function validate(string $crontab) {
    }
}
