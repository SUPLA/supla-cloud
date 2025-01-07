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

namespace SuplaBundle\Tests\Model\Schedule\SchedulePlanner;

use DateTime;
use PHPUnit\Framework\TestCase;
use SuplaBundle\Model\Schedule\SchedulePlanners\CronExpressionSchedulePlanner;
use SuplaBundle\Model\Schedule\SchedulePlanners\OnceSchedulePlanner;

class OnceSchedulePlannerTest extends TestCase {
    /**
     * @dataProvider calculatingNextRunDateProvider
     */
    public function testCalculatingNextRunDate($startDate, $cronExpression, $expectedNextRunDate) {
        $schedulePlanner = new OnceSchedulePlanner();
        $format = 'Y-m-d H:i';
        $startDate = DateTime::createFromFormat($format, $startDate);
        $this->assertTrue($schedulePlanner->canCalculateFor($cronExpression));
        $nextExecution = $schedulePlanner->calculateNextScheduleExecution($cronExpression, $startDate);
        $this->assertEquals($expectedNextRunDate, $nextExecution->format($format));
    }

    public static function calculatingNextRunDateProvider() {
        return [
            ['2017-01-01 00:00', '23 11 5 12 * 2089', '2089-12-05 11:23'], // run once
        ];
    }

    /**
     * @dataProvider invalidCronExpressions
     */
    public function testDoesNotSupportInvalidCronExpressions($invalidCronExpression) {
        $schedulePlanner = new CronExpressionSchedulePlanner();
        $this->assertFalse($schedulePlanner->canCalculateFor($invalidCronExpression));
    }

    public static function invalidCronExpressions() {
        return [
            [''],
            ['S * * * *'],
            ['S * * * * 2050'],
        ];
    }
}
