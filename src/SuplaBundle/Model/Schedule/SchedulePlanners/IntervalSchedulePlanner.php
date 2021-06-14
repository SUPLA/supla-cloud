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

class IntervalSchedulePlanner extends SchedulePlanner {

    const CRON_EXPRESSION_INTERVAL_REGEX = '#^\*/(\d{1,7})( \*)*$#';

    public function calculateNextScheduleExecution(string $crontab, DateTime $currentDate): DateTime {
        preg_match(self::CRON_EXPRESSION_INTERVAL_REGEX, $crontab, $matches);
        $intervalInMinutes = intval($matches[1]);
        $period = "PT{$intervalInMinutes}M";
        $nextRunDate = clone $currentDate;
        if ($nextRunDate->getTimezone()->getName() != 'UTC') {
            // switching to UTC before adding the interval mitigates problems with DST changes
            $nextRunDate->setTimezone(new DateTimeZone('UTC'));
        }
        $nextRunDate->add(new DateInterval($period));
        $nextRunDate = CompositeSchedulePlanner::roundToClosestMinute($nextRunDate, $nextRunDate->getTimezone());
        return $nextRunDate;
    }

    public function canCalculateFor(string $crontab): bool {
        $valid = preg_match(self::CRON_EXPRESSION_INTERVAL_REGEX, $crontab, $matches);
        return $valid && intval($matches[1]) > 0;
    }
}
