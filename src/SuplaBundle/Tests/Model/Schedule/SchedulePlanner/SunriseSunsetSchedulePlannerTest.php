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

// @codingStandardsIgnoreFile

use DateTime;
use DateTimeZone;
use PHPUnit_Framework_TestCase;
use SuplaBundle\Model\Schedule\SchedulePlanners\SunriseSunsetSchedulePlanner;

date_default_timezone_set('UTC');

class SunriseSunsetSchedulePlannerTest extends PHPUnit_Framework_TestCase {
    /**
     * @dataProvider calculatingNextRunDateProvider
     */
    public function testCalculatingNextRunDate($startDate, $cronExpression, $expectedNextRunDate, $timezone = 'Europe/Warsaw') {
        $schedulePlanner = new SunriseSunsetSchedulePlanner();
        $schedule = new ScheduleWithTimezone($cronExpression, $timezone);
        $format = 'Y-m-d H:i';
        $startDate = DateTime::createFromFormat($format, $startDate, new DateTimeZone($timezone));
        $this->assertTrue($schedulePlanner->canCalculateFor($schedule));
        $nextRunDate = $schedulePlanner->calculateNextScheduleExecution($schedule, $startDate);
        $this->assertEquals($expectedNextRunDate, $nextRunDate->format($format));
    }

    public function calculatingNextRunDateProvider() {
        return [
            ['2017-01-01 00:00', 'SR0 * * * *', '2017-01-01 07:45'], // sunrise: 7:46, http://suncalc.net/#/52.2297,21.0122,11/2017.01.01/13:11
            ['2016-12-31 23:00', 'SR0 * * * *', '2017-01-01 07:45'], // sunrise: 7:46, http://suncalc.net/#/52.2297,21.0122,11/2017.01.01/13:11
            ['2017-01-01 00:00', 'SS0 * * * *', '2017-01-01 15:35'], // sunset: 15:34
            ['2017-06-24 00:00', 'SR0 * * * *', '2017-06-24 04:15'], // sunrise: 4:15, http://suncalc.net/#/52.2297,21.0122,11/2017.06.24/13:11
            ['2017-06-24 00:00', 'SS0 * * * *', '2017-06-24 21:00'], // sunrise: 21:01
            ['2017-06-29 00:00', 'SR0 * * * *', '2017-06-29 04:20'], // sunrise: 4:18, http://suncalc.net/#/52.2297,21.0122,11/2017.06.29/13:11
            ['2017-06-29 00:00', 'SS0 * * * *', '2017-06-29 21:00'], // sunset: 21:02
            ['2017-06-28 23:59', 'SS0 * * * *', '2017-06-29 16:55', 'Australia/Sydney'], // sunset: 21:02
            ['2017-06-29 00:00', 'SS0 * * * *', '2017-06-29 16:55', 'Australia/Sydney'], // sunset: 21:02
            ['2017-06-29 00:01', 'SS0 * * * *', '2017-06-29 16:55', 'Australia/Sydney'], // sunset: 21:02
            ['2017-06-24 04:08', 'SR0 * * * *', '2017-06-24 04:15'],
            ['2017-06-24 04:14', 'SR0 * * * *', '2017-06-25 04:15'],
            ['2017-06-24 04:13', 'SR5 * * * *', '2017-06-24 04:20'],
            ['2017-06-24 04:14', 'SR15 * * * *', '2017-06-24 04:30'],
            ['2017-06-24 04:14', 'SR-5 * * * *', '2017-06-25 04:10'],
            ['2017-06-24 04:00', 'SR-5 * * * *', '2017-06-24 04:10'],
            ['2017-06-24 01:00', 'SR-15 * * * *', '2017-06-24 04:00'],
            ['2017-06-24 01:00', 'SR-150 * * * *', '2017-06-24 01:45'],
            ['2017-06-24 01:00', 'SR150 * * * *', '2017-06-24 06:45'],
            ['2017-06-24 04:15', 'SR0 * * * *', '2017-06-25 04:15'],
            ['2017-06-24 06:15', 'SR0 * * * *', '2017-06-25 04:15'],
            ['2017-02-18 13:51', 'SR0 * * * *', '2017-02-19 06:45'],
            ['2017-02-18 13:51', 'SS0 * * * *', '2017-02-18 16:55'],
            ['2017-02-18 13:51', 'SS0 * * * 1,2,3,4,5', '2017-02-20 17:00'], // only chosen weekdays
//           saturday                            friday
            ['2017-02-18 13:51', 'SR0 * * * 5', '2017-02-24 06:30'],
//           saturday                            next saturday
            ['2017-02-18 13:51', 'SR0 * * * 6', '2017-02-25 06:30'],
            ['2017-02-19 13:51', 'SR0 * * * 6', '2017-02-25 06:30'],
            ['2017-02-18 13:51', 'SR0 * 14 3 *', '2017-03-14 05:50'],
            ['2017-02-19 11:36', 'SS0 * * * *', '2017-02-19 17:45', 'Asia/Shanghai'], // it caused stackoverflow in the past
            ['2017-11-19 11:36', 'SS0 * * * *', '2017-11-19 17:50', 'Asia/Colombo'], // GMT+5.5
            ['2017-03-25 18:13', 'SS0 * * * *', '2017-03-25 18:20', 'Asia/Colombo'], // GMT+5.5
            ['2017-03-25 18:20', 'SS0 * * * *', '2017-03-26 18:20', 'Asia/Colombo'], // GMT+5.5
            ['2017-03-25 18:21', 'SS0 * * * *', '2017-03-26 18:20', 'Asia/Colombo'], // GMT+5.5
        ];
    }

    // https://github.com/SUPLA/supla-cloud/issues/78
    public function testNextRunDateIsAlwaysOnTheNextDay() {
        $schedulePlanner = new SunriseSunsetSchedulePlanner();
        $schedule = new ScheduleWithTimezone('SS0 * * * *', 'Europe/Warsaw');
        $format = 'Y-m-d H:i';
        $startDate = DateTime::createFromFormat($format, '2017-04-23 15:00', new DateTimeZone('Europe/Warsaw'));
        $nextRunDate = $schedulePlanner->calculateNextScheduleExecution($schedule, $startDate);
        $this->assertEquals('2017-04-23 19:50', $nextRunDate->format($format));
        $nextRunDate = $schedulePlanner->calculateNextScheduleExecution($schedule, $nextRunDate);
        $this->assertEquals('2017-04-24 19:50', $nextRunDate->format($format));
    }

    // https://github.com/SUPLA/supla-cloud/issues/405
    public function testDoubleGenerationOfScheduleExection() {
        $schedulePlanner = new SunriseSunsetSchedulePlanner();
        $schedule = new ScheduleWithTimezone('SR0 * * * *', 'Europe/Warsaw');
        $format = 'Y-m-d H:i';
        $startDate = DateTime::createFromFormat($format, '2020-11-03 01:00', new DateTimeZone('Europe/Warsaw'));
        $nextRunDate = $schedulePlanner->calculateNextScheduleExecution($schedule, $startDate);
        $this->assertEquals('2020-11-03 06:35', $nextRunDate->format($format));
        $nextRunDate = $schedulePlanner->calculateNextScheduleExecution($schedule, $nextRunDate);
        $this->assertEquals('2020-11-04 06:35', $nextRunDate->format($format));
        $nextRunDate = $schedulePlanner->calculateNextScheduleExecution($schedule, $nextRunDate);
        $this->assertEquals('2020-11-05 06:40', $nextRunDate->format($format));
    }
}
