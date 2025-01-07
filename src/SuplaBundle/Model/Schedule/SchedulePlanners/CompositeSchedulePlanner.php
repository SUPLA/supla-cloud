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

use Assert\Assertion;
use DateTime;
use DateTimeZone;
use InvalidArgumentException;
use SuplaBundle\Entity\Main\Schedule;
use SuplaBundle\Entity\Main\ScheduledExecution;
use SuplaBundle\Enums\ChannelFunctionAction;
use SuplaBundle\Utils\DateUtils;

class CompositeSchedulePlanner {
    /** @var SchedulePlanner[] */
    private $planners;

    public function __construct(iterable $planners) {
        $this->planners = $planners;
    }

    public function calculateNextScheduleExecution(Schedule $schedule, DateTime $currentDate): ScheduledExecution {
        return CompositeSchedulePlanner::wrapInScheduleTimezone($schedule, function () use ($schedule, $currentDate) {
            $closestExecution = null;
            foreach ($schedule->getConfig() as $executionDef) {
                $nextRunDate = $this->nextRunDate($executionDef['crontab'] ?? '', $currentDate);
                if (!$closestExecution || $closestExecution->getPlannedTimestamp() > $nextRunDate) {
                    $closestExecution = new ScheduledExecution(
                        $schedule,
                        $nextRunDate,
                        $executionDef['action']['id'] ?? false ? new ChannelFunctionAction($executionDef['action']['id']) : null,
                        $executionDef['action']['param'] ?? null
                    );
                }
            }
            return $closestExecution;
        });
    }

    private function nextRunDate(string $crontab, DateTime $currentDate): DateTime {
        foreach ($this->planners as $planner) {
            if ($planner->canCalculateFor($crontab)) {
                return $planner->calculateNextScheduleExecution($crontab, $currentDate);
            }
        }
        throw new \RuntimeException("Could not calculate the next run date for the expression: {$crontab}");
    }

    /**
     * @param Schedule $schedule
     * @param string $currentDate
     * @param string $until
     * @param int $maxCount
     * @return \SuplaBundle\Entity\Main\ScheduledExecution[]
     */
    public function calculateScheduleExecutionsUntil(Schedule $schedule, $until = '+5days', $currentDate = 'now', $maxCount = PHP_INT_MAX) {
        return CompositeSchedulePlanner::wrapInScheduleTimezone($schedule, function () use ($schedule, $until, $currentDate, $maxCount) {
            $until = is_int($until) ? $until : strtotime($until) + 1; // +1 to make it inclusive
            if (!($currentDate instanceof DateTime)) {
                $currentDate = new DateTime($currentDate, $schedule->getUserTimezone());
            }
            $scheduleExecutions = [];
            $nextExecution = new ScheduledExecution($schedule, $currentDate);
            try {
                do {
                    $nextExecution = $this->calculateNextScheduleExecution($schedule, $nextExecution->getPlannedTimestamp());
                    $scheduleExecutions[] = $nextExecution;
                } while ($nextExecution->getPlannedTimestamp()->getTimestamp() < $until && count($scheduleExecutions) < $maxCount);
            } catch (\RuntimeException | InvalidArgumentException $e) {
                // impossible cron expression
            }
            if ($nextExecution->getPlannedTimestamp()->getTimezone()->getName() != $schedule->getUser()->getTimezone()) {
                $scheduleExecutions = array_map(function (ScheduledExecution $executionInDifferentTimezone) use ($schedule) {
                    $fixedDateTime = $executionInDifferentTimezone->getPlannedTimestamp()->setTimezone($schedule->getUserTimezone());
                    return new ScheduledExecution(
                        $schedule,
                        $fixedDateTime,
                        $executionInDifferentTimezone->getAction(),
                        $executionInDifferentTimezone->getActionParam()
                    );
                }, $scheduleExecutions);
            }
            return $scheduleExecutions;
        });
    }

    public function validateCrontab(string $crontab) {
        $canCalculate = false;
        foreach ($this->planners as $planner) {
            if ($planner->canCalculateFor($crontab)) {
                $canCalculate = true;
                $planner->validate($crontab);
            }
        }
        Assertion::true($canCalculate, 'Invalid schedule.');
    }

    public static function wrapInScheduleTimezone(Schedule $schedule, $function): mixed {
        return DateUtils::wrapInTimezone($schedule->getUserTimezone(), $function);
    }

    public static function roundToClosestMinute($dateOrTimestamp, DateTimeZone $timezone): DateTime {
        $timestamp = is_int($dateOrTimestamp) ? $dateOrTimestamp : $dateOrTimestamp->getTimestamp();
        $roundTimestamp = round($timestamp / 60) * 60;
        return (new DateTime('now', $timezone))->setTimestamp($roundTimestamp);
    }
}
