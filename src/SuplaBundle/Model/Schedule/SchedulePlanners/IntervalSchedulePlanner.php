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

use DateInterval;
use DateTime;
use DateTimeZone;
use SuplaBundle\Entity\Schedule;
use SuplaBundle\Entity\ScheduledExecution;

class IntervalSchedulePlanner implements SchedulePlanner {

    const CRON_EXPRESSION_INTERVAL_REGEX = '#^\*/(\d{1,3})( \*)*$#';

    public function calculateNextScheduleExecution(Schedule $schedule, DateTime $currentDate) {
        preg_match(self::CRON_EXPRESSION_INTERVAL_REGEX, $schedule->getTimeExpression(), $matches);
        $intervalInMinutes = intval($matches[1]);
        $period = "PT{$intervalInMinutes}M";
        $nextRunDate = clone $currentDate;
        if ($nextRunDate->getTimezone()->getName() != 'UTC') {
            // switching to UTC before adding the interval mitigates problems with DST changes
            $nextRunDate->setTimezone(new DateTimeZone('UTC'));
        }
        $nextRunDate->add(new DateInterval($period));
        $nextRunDate = CompositeSchedulePlanner::roundToClosest5Minutes($nextRunDate, $nextRunDate->getTimezone());
        return new ScheduledExecution($schedule, $nextRunDate);
    }

    public function canCalculateFor(Schedule $schedule) {
        return !!preg_match(self::CRON_EXPRESSION_INTERVAL_REGEX, $schedule->getTimeExpression());
    }
}
