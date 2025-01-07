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

class CronExpressionSchedulePlannerTest extends TestCase {
    /**
     * @dataProvider calculatingNextRunDateProvider
     */
    public function testCalculatingNextRunDate($startDate, $cronExpression, $expectedNextRunDate) {
        $schedulePlanner = new CronExpressionSchedulePlanner();
        $format = 'Y-m-d H:i';
        $startDate = DateTime::createFromFormat($format, $startDate);
        $this->assertTrue($schedulePlanner->canCalculateFor($cronExpression));
        $nextExecution = $schedulePlanner->calculateNextScheduleExecution($cronExpression, $startDate);
        $this->assertEquals($expectedNextRunDate, $nextExecution->format($format));
    }

    public static function calculatingNextRunDateProvider() {
        return [
            ['2017-01-01 00:00', '*/5 * * * *', '2017-01-01 00:05'], // every 5 minutes
            ['2017-01-01 00:00', '34 12 * * 4', '2017-01-05 12:34'], // 12:34 in thursdays
            ['2017-01-01 00:00', '0 23 13 * *', '2017-01-13 23:00'], // 13 day of month, 23:00
            ['2017-01-01 00:00', '0 7 29 2 *', '2020-02-29 07:00'], // 29 February, 7:00 from 2017 year
            ['2021-01-01 00:00', '0 7 29 2 *', '2024-02-29 07:00'], // 29 February, 7:00 from 2021 year
            ['2021-01-01 00:00', '0 3,19 * * *', '2021-01-01 03:00'],
            ['2021-01-01 00:00', '0 3,19 * * WED#2,MON#3', '2021-01-13 03:00'],
            ['2021-01-01 00:00', '0 3,19 L * *', '2021-01-31 03:00'],
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
        ];
    }
}
