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

use SuplaBundle\Model\Schedule\SchedulePlanners\CompositeSchedulePlanner;
use SuplaBundle\Model\Schedule\SchedulePlanners\CronExpressionSchedulePlanner;
use SuplaBundle\Model\Schedule\SchedulePlanners\IntervalSchedulePlanner;
use SuplaBundle\Model\Schedule\SchedulePlanners\SunriseSunsetSchedulePlanner;

class CompositeSchedulePlannerTest extends \PHPUnit_Framework_TestCase {
    /** @var CompositeSchedulePlanner */
    private $planner;

    public function setUp() {
        $this->planner = new CompositeSchedulePlanner([
            new IntervalSchedulePlanner(),
            new CronExpressionSchedulePlanner(),
            new SunriseSunsetSchedulePlanner(),
        ]);
    }

    public function testCalculatingRunDatesUntil() {
        $schedule = new ScheduleWithTimezone('*/5 * * * *', 'Europe/Warsaw');
        $runDates = array_map(function (\DateTime $d) {
            return $d->format(\DateTime::ATOM);
        }, $this->planner->calculateNextRunDatesUntil($schedule, '2017-01-02 00:00', '2017-01-01 00:00'));
        $this->assertNotContains('2017-01-01T00:00:00+01:00', $runDates);
        $this->assertContains('2017-01-01T00:05:00+01:00', $runDates);
        $this->assertContains('2017-01-01T00:15:00+01:00', $runDates);
        $this->assertContains('2017-01-01T22:35:00+01:00', $runDates);
        $this->assertContains('2017-01-02T00:00:00+01:00', $runDates);
        $this->assertNotContains('2017-01-02T00:10:00+01:00', $runDates);
    }

    public function testCalculatingWhenDstChanges() {
        $schedule = new ScheduleWithTimezone('*/5 * * * *', 'Europe/Warsaw');
        $runDates = array_map(function (\DateTime $d) {
            return $d->format(\DateTime::ATOM);
        }, $this->planner->calculateNextRunDatesUntil($schedule, '2017-03-26 04:00', '2017-03-26 01:00'));
        $this->assertNotContains('2017-03-26T02:00:00+01:00', $runDates);
    }

    public function testCalculatingRunForMelbourne() {
        $schedule = new ScheduleWithTimezone('*/5 * * * *', 'Australia/Melbourne');
        $runDates = array_map(function (\DateTime $d) {
            return $d->format(\DateTime::ATOM);
        }, $this->planner->calculateNextRunDatesUntil($schedule, '2017-01-01 01:00', '2017-01-01 00:00'));
        $this->assertNotContains('2017-01-01T00:00:00+11:00', $runDates);
        $this->assertContains('2017-01-01T00:30:00+11:00', $runDates);
        $this->assertContains('2017-01-01T01:00:00+11:00', $runDates);
        $this->assertNotContains('2017-01-01T01:10:00+01:00', $runDates);
    }

    public function testCalculatingRunDatesUntilIfTheFirstOneIsLater() {
        $schedule = new ScheduleWithTimezone();
        $schedule->setTimeExpression('23 11 5 12 * 2089');
        $runDates = array_map(function (\DateTime $d) {
            return $d->format(\DateTime::ATOM);
        }, $this->planner->calculateNextRunDatesUntil($schedule, '2017-01-01 00:00'));
        $this->assertCount(1, $runDates);
        $this->assertContains('2089-12-05T11:23:00+01:00', $runDates);
    }

    public function testCalculatingRunDatesUntilDoesNotThrowAnErrorIfNoMoreDates() {
        $schedule = new ScheduleWithTimezone();
        $schedule->setTimeExpression('23 11 5 12 * 2089');
        $runDates = array_map(function ($d) {
            return $d->format(\DateTime::ATOM);
        }, $this->planner->calculateNextRunDatesUntil($schedule, '2099-01-01 00:00'));
        $this->assertCount(1, $runDates);
        $this->assertContains('2089-12-05T11:23:00+01:00', $runDates);
    }

    public function testCalculatingManyDates() {
        $schedule = new ScheduleWithTimezone();
        $schedule->setTimeExpression('*/5 * * * *');
        $dates = $this->planner->calculateNextRunDatesUntil($schedule, '+5 days');
        $this->assertGreaterThan(1000, count($dates));
    }

    // https://github.com/SUPLA/supla-cloud/issues/78
    public function testNextRunDateIsAlwaysOnTheNextDayForSunsetBasedSchedule() {
        $schedule = new ScheduleWithTimezone('SS0 * * * *', 'Europe/Warsaw');
        $nextRunDates = $this->planner->calculateNextRunDatesUntil($schedule, '2017-04-24 15:00:00', '2017-04-22 15:00:00');
        $formattedNextRunDates = array_map(function ($nextRunDate) {
            return $nextRunDate->format('Y-m-d H:i');
        }, $nextRunDates);
        $this->assertEquals([
            '2017-04-22 19:45',
            '2017-04-23 19:50',
            '2017-04-24 19:50',
        ], $formattedNextRunDates);
    }

    public function testMinutesBasedSchedulesAreRelativeToStartTime() {
        $schedule = new ScheduleWithTimezone();
        $schedule->setTimeExpression('*/10 * * * *');
        $format = 'Y-m-d H:i';
        $this->assertEquals('2017-07-01 15:10', $this->planner->calculateNextRunDate($schedule, '2017-07-01 15:00:00')->format($format));
        $this->assertEquals('2017-07-01 15:10', $this->planner->calculateNextRunDate($schedule, '2017-07-01 15:01:00')->format($format));
        $this->assertEquals('2017-07-01 15:15', $this->planner->calculateNextRunDate($schedule, '2017-07-01 15:05:00')->format($format));
    }

    public function testCalculatingIntervalDatesWithSpecificStartTime() {
        $schedule = new ScheduleWithTimezone('*/35 * * * *', 'Europe/Warsaw');
        $runDates = array_map(function (\DateTime $d) {
            return $d->format(\DateTime::ATOM);
        }, $this->planner->calculateNextRunDatesUntil($schedule, '2017-01-02 00:00', '2017-01-01 04:33'));
        $this->assertNotContains('2017-01-01T05:00:00+01:00', $runDates);
        $this->assertNotContains('2017-01-01T05:05:00+01:00', $runDates);
        $this->assertNotContains('2017-01-01T00:05:00+01:00', $runDates);
        $this->assertContains('2017-01-01T05:10:00+01:00', $runDates);
        $this->assertContains('2017-01-01T05:45:00+01:00', $runDates);
        $this->assertContains('2017-01-01T23:50:00+01:00', $runDates);
        $this->assertNotContains('2017-01-02T01:00:00+01:00', $runDates);
    }
}
