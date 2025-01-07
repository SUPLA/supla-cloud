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

use Cron\CronExpression;
use DateInterval;
use DateTime;
use DateTimeZone;

class SunriseSunsetSchedulePlanner extends SchedulePlanner {

    // SR -> SunRise, SS -> SunSet
    private const SPECIFICATION_REGEX = '#^S([SR])(-?\d+)#';
    private const MINIMUM_SECONDS_TO_NEXT_SUN = 360;
    private const DEFAULT_COORDINATES = ['latitude' => 52.25, 'longitude' => 21];

    /** @inheritdoc */
    public function calculateNextScheduleExecution(string $crontab, DateTime $currentDate): DateTime {
        $currentDate = $this->getNextDueDate($crontab, $currentDate);
        $nextRunDate = $this->calculateNextRunDateBasedOnSun($crontab, $currentDate);
        $retries = 5; // PHP sometimes returns past sunset even if we query for midnight of the next day...
        $calculatingFromDate = clone max($nextRunDate, $currentDate);
        while ((($nextRunDate->getTimestamp() <= $currentDate->getTimestamp() + self::MINIMUM_SECONDS_TO_NEXT_SUN)
                || (!$this->isDue($crontab, $nextRunDate))) && --$retries) {
            $nextRunDate->setTime(0, 0);
            while ($nextRunDate <= $calculatingFromDate) {
                $nextRunDate->add(new DateInterval('P1D'));
            }
            $calculatingFromDate = $this->getNextDueDate($crontab, $nextRunDate);
            $nextRunDate = $this->calculateNextRunDateBasedOnSun($crontab, $calculatingFromDate);
            while ($nextRunDate <= $calculatingFromDate) {
                $nextRunDate->add(new DateInterval('P1D'));
            }
        }
        return $nextRunDate;
    }

    private function calculateNextRunDateBasedOnSun(string $crontab, DateTime $currentDate) {
        preg_match(self::SPECIFICATION_REGEX, $crontab, $matches);
        $timezone = new DateTimeZone(date_default_timezone_get());
        $location = $timezone->getLocation() ?: self::DEFAULT_COORDINATES;
        $lookFor = $matches[1] == 'S' ? 'sunset' : 'sunrise';
        $sunInfo = date_sun_info($currentDate->getTimestamp(), $location['latitude'], $location['longitude']);
        $nextSun = $sunInfo[$lookFor];
        $nextSun += intval($matches[2]) * 60;
        $nextRunDate = CompositeSchedulePlanner::roundToClosestMinute($nextSun, $timezone);
        if ($nextRunDate < $currentDate) {
            // PHP sometimes returns past sunset even if we query for midnight of the next day...
            $nextRunDate->add(new DateInterval('P1D'));
        } elseif ($nextRunDate == $currentDate) {
            // if it is sunset now, let's calculate for the next day
            return $this->calculateNextRunDateBasedOnSun($crontab, $nextRunDate->add(new DateInterval('PT12H')));
        }
        return $nextRunDate;
    }

    /** @return DateTime */
    private function getNextDueDate(string $crontab, DateTime $currentDate) {
        if (!$this->isDue($crontab, $currentDate)) {
            return $this->getEveryMinuteCronExpression($crontab)->getNextRunDate($currentDate);
        }
        return $currentDate;
    }

    private function isDue(string $crontab, DateTime $currentDate) {
        return $this->getEveryMinuteCronExpression($crontab)->isDue($currentDate);
    }

    /** @return CronExpression */
    private function getEveryMinuteCronExpression(string $crontab) {
        $parts = explode(' ', $crontab);
        $parts[0] = '*';
        $parts[1] = '*';
        return new CronExpression(implode(' ', $parts));
    }

    public function canCalculateFor(string $crontab): bool {
        return !!preg_match(self::SPECIFICATION_REGEX, $crontab);
    }
}
