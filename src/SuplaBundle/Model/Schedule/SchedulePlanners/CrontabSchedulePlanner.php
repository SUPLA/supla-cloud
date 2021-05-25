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
use Cron\CronExpression;
use DateTime;
use SuplaBundle\Entity\Schedule;
use SuplaBundle\Entity\ScheduledExecution;
use SuplaBundle\Enums\ChannelFunctionAction;
use SuplaBundle\Enums\ScheduleMode;
use SuplaBundle\Utils\ArrayUtils;

class CrontabSchedulePlanner implements SchedulePlanner {
    /** @var CompositeSchedulePlanner */
    private $compositePlanner;

    public function calculateNextScheduleExecution(Schedule $schedule, DateTime $currentDate): ScheduledExecution {
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
        return new ScheduledExecution($schedule, $closestExecution->getPlannedTimestamp(), $closestExecution->getAction(), $closestExecution->getActionParam());
    }

    public function canCalculateFor(Schedule $schedule): bool {
        return $schedule->getMode()->getValue() === ScheduleMode::CRONTAB && $schedule->getConfig() && is_array($schedule->getConfig());
    }

    public function validate(Schedule $schedule) {
        Assertion::null($schedule->getTimeExpression(), 'Daily schedule in an old format. Use config instead.');
        Assertion::isArray($schedule->getConfig(), 'Invalid schedule config (not an array).');
        foreach (array_values($schedule->getConfig()) as $configItem) {
            $configItem = ArrayUtils::leaveKeys($configItem, ['crontab', 'action']);
            Assertion::count($configItem, 2, 'Invalid schedule config (incorrect config item).');
            Assertion::string($configItem['crontab'], 'Invalid schedule config (incorrect crontab).');
            Assertion::isArray($configItem['action'], 'Invalid schedule config (incorrect action).');
            $action = ArrayUtils::leaveKeys($configItem['action'], ['id', 'param']);
            Assertion::between(count($action), 1, 2, 'Invalid schedule config (incorrect action).');
            Assertion::keyExists($action, 'id', 'Invalid schedule config (no action ID).');
            Assertion::true(ChannelFunctionAction::isValid($action['id']), 'Invalid schedule config (incorrect action ID).');
            Assertion::isArray($action['param'] ?? [], 'Invalid schedule config (incorrect action param).');
            $configItem['action'] = $action;
//            CronExpression::isValidExpression($configItem['crontab']);
        }
    }

    /** @required */
    public function setCompositePlanner(CompositeSchedulePlanner $compositePlanner): void {
        $this->compositePlanner = $compositePlanner;
    }
}
