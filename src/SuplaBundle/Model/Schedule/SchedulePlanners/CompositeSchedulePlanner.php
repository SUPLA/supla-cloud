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

use SensioLabs\Security\Exception\RuntimeException;
use SuplaBundle\Entity\Schedule;

class CompositeSchedulePlanner {
    /** @var SchedulePlanner[] */
    private $planners;

    public function __construct(array $planners) {
        $this->planners = $planners;
    }

    public function calculateNextRunDate(Schedule $schedule, $currentDate = 'now') {
        return CompositeSchedulePlanner::wrapInScheduleTimezone($schedule, function () use ($schedule, $currentDate) {
            if (!($currentDate instanceof \DateTime)) {
                $currentDate = new \DateTime($currentDate, $schedule->getUserTimezone());
            }
            foreach ($this->planners as $planner) {
                if ($planner->canCalculateFor($schedule)) {
                    return $planner->calculateNextRunDate($schedule, $currentDate);
                }
            }
            throw new RuntimeException("Could not calculate the next run date for the Schedule#{$schedule->getId()}. "
                . "Expression: {$schedule->getTimeExpression()}");
        });
    }

    /**
     * @param Schedule $schedule
     * @param string $currentDate
     * @param string $until
     * @param int $maxCount
     * @return \DateTime[]
     */
    public function calculateNextRunDatesUntil(Schedule $schedule, $until = '+5days', $currentDate = 'now', $maxCount = PHP_INT_MAX) {
        return CompositeSchedulePlanner::wrapInScheduleTimezone($schedule, function () use ($schedule, $until, $currentDate, $maxCount) {
            $until = is_int($until) ? $until : strtotime($until) + 1; // +1 to make it inclusive
            $runDates = [];
            $nextRunDate = $currentDate;
            try {
                do {
                    $nextRunDate = $this->calculateNextRunDate($schedule, $nextRunDate);
                    $runDates[] = $nextRunDate;
                } while ($nextRunDate->getTimestamp() < $until && count($runDates) < $maxCount);
            } catch (\RuntimeException $e) {
                // impossible cron expression
            }
            if ($nextRunDate->getTimezone()->getName() != $schedule->getUser()->getTimezone()) {
                $runDates = array_map(function (\DateTime $nextRunDateInDifferentTimezone) use ($schedule) {
                    $nextRunDateInDifferentTimezone->setTimezone($schedule->getUserTimezone());
                    return $nextRunDateInDifferentTimezone;
                }, $runDates);
            }
            return $runDates;
        });
    }

    public static function wrapInScheduleTimezone(Schedule $schedule, $function) {
        $defaultTimezone = date_default_timezone_get();
        date_default_timezone_set($schedule->getUser()->getTimezone());
        $result = $function();
        date_default_timezone_set($defaultTimezone);
        return $result;
    }

    public static function roundToClosest5Minutes($dateOrTimestamp, \DateTimeZone $timezone): \DateTime {
        $timestamp = is_int($dateOrTimestamp) ? $dateOrTimestamp : $dateOrTimestamp->getTimestamp();
        $timestampRoundTo5Minutes = round($timestamp / 300) * 300;
        return (new \DateTime('now', $timezone))->setTimestamp($timestampRoundTo5Minutes);
    }
}
