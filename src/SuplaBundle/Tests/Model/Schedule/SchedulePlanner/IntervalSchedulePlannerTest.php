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
use SuplaBundle\Model\Schedule\SchedulePlanners\IntervalSchedulePlanner;

class IntervalSchedulePlannerTest extends TestCase {
    /**
     * @dataProvider calculatingNextRunDateProvider
     */
    public function testCalculatingNextRunDate($startDate, $cronExpression, $expectedNextRunDate) {
        $schedulePlanner = new IntervalSchedulePlanner();
        $format = 'Y-m-d H:i';
        $startDate = DateTime::createFromFormat($format, $startDate);
        $this->assertTrue($schedulePlanner->canCalculateFor($cronExpression));
        $nextExecution = $schedulePlanner->calculateNextScheduleExecution($cronExpression, $startDate);
        $this->assertEquals($expectedNextRunDate, $nextExecution->format($format));
    }

    public static function calculatingNextRunDateProvider() {
        return [
            ['2017-01-01 00:00', '*/5 * * * *', '2017-01-01 00:05'],
            ['2017-01-01 00:05', '*/5 * * * *', '2017-01-01 00:10'],
            ['2017-01-01 00:01', '*/5 * * * *', '2017-01-01 00:06'],
            ['2017-01-01 00:01', '*/2 * * * *', '2017-01-01 00:03'],
            ['2017-01-01 00:01', '*/1 * * * *', '2017-01-01 00:02'],
            ['2017-01-01 00:00', '*/10 * * * *', '2017-01-01 00:10'],
            ['2017-01-01 00:05', '*/10 * * * *', '2017-01-01 00:15'],
            ['2017-01-01 00:04', '*/10 * * * *', '2017-01-01 00:14'],
            ['2017-01-01 00:04', '*/10', '2017-01-01 00:14'],
            ['2017-01-01 14:56', '*/10 * * * *', '2017-01-01 15:06'],
            ['2017-01-01 17:07', '*/45 * * * *', '2017-01-01 17:52'],
            ['2017-01-01 17:08', '*/45 * * * *', '2017-01-01 17:53'],
            ['2017-03-26 01:00', '*/1440 * * * *', '2017-03-27 01:00'],
            ['2017-03-25 02:30', '*/1440 * * * *', '2017-03-26 02:30'],
            ['2017-03-25 02:30', '*/9999999 * * * *', '2036-03-29 13:09'],
        ];
    }

    /**
     * @dataProvider invalidCronExpressions
     */
    public function testDoesNotSupportInvalidIntervalExpressions($invalidCronExpression) {
        $schedulePlanner = new IntervalSchedulePlanner();
        $this->assertFalse($schedulePlanner->canCalculateFor($invalidCronExpression));
    }

    public static function invalidCronExpressions() {
        return [
            [''],
            ['S * * * *'],
            ['*/5 2 * * *'],
            ['5 * * * *'],
            ['*/0 * * * *'],
            ['*/99999999 * * * *'],
        ];
    }
}
