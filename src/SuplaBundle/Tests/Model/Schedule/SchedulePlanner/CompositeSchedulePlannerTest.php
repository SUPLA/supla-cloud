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
use DateTimeZone;
use PHPUnit\Framework\TestCase;
use SuplaBundle\Entity\Main\ScheduledExecution;
use SuplaBundle\Enums\ChannelFunctionAction;
use SuplaBundle\Enums\ScheduleMode;
use SuplaBundle\Model\Schedule\SchedulePlanners\CompositeSchedulePlanner;
use SuplaBundle\Model\Schedule\SchedulePlanners\CronExpressionSchedulePlanner;
use SuplaBundle\Model\Schedule\SchedulePlanners\IntervalSchedulePlanner;
use SuplaBundle\Model\Schedule\SchedulePlanners\OnceSchedulePlanner;
use SuplaBundle\Model\Schedule\SchedulePlanners\SunriseSunsetSchedulePlanner;

class CompositeSchedulePlannerTest extends TestCase {
    /** @var CompositeSchedulePlanner */
    private $planner;

    public function setUp(): void {
        $this->planner = new CompositeSchedulePlanner([
            new OnceSchedulePlanner(),
            new IntervalSchedulePlanner(),
            new CronExpressionSchedulePlanner(),
            new SunriseSunsetSchedulePlanner(),
        ]);
    }

    public static function formatPlannedTimestamp($format = DateTime::ATOM) {
        return function (ScheduledExecution $scheduledExecution) use ($format) {
            return $scheduledExecution->getPlannedTimestamp()->format($format);
        };
    }

    public function testCalculatingRunDatesUntil() {
        $schedule = new ScheduleWithTimezone('*/5 * * * *', 'Europe/Warsaw');
        $runDates = array_map(
            self::formatPlannedTimestamp(),
            $this->planner->calculateScheduleExecutionsUntil($schedule, '2017-01-02 00:00', '2017-01-01 00:00')
        );
        $this->assertNotContains('2017-01-01T00:00:00+01:00', $runDates);
        $this->assertContains('2017-01-01T00:05:00+01:00', $runDates);
        $this->assertContains('2017-01-01T00:15:00+01:00', $runDates);
        $this->assertContains('2017-01-01T22:35:00+01:00', $runDates);
        $this->assertContains('2017-01-02T00:00:00+01:00', $runDates);
        $this->assertNotContains('2017-01-02T00:10:00+01:00', $runDates);
    }

    public function testCalculatingIntervalWhenDstChangesForward() {
        $schedule = new ScheduleWithTimezone('*/5 * * * *', 'Europe/Warsaw');
        $runDates = array_map(
            self::formatPlannedTimestamp(),
            $this->planner->calculateScheduleExecutionsUntil($schedule, '2017-03-26 04:00', '2017-03-26 01:00')
        );
        $this->assertNotContains('2017-03-26T02:00:00+01:00', $runDates);
    }

    public function testCalculatingCronExpressionWhenDstChangesForward() {
        $schedule = new ScheduleWithTimezone('30 2 * * *', 'Europe/Warsaw');
        $runDates = array_map(
            self::formatPlannedTimestamp(),
            $this->planner->calculateScheduleExecutionsUntil($schedule, '2017-03-28 08:00', '2017-03-25 00:00')
        );
        $this->assertContains('2017-03-25T02:30:00+01:00', $runDates);
        $this->assertContains('2017-03-26T03:30:00+02:00', $runDates);
        $this->assertContains('2017-03-27T02:30:00+02:00', $runDates);
        $this->assertNotContains('2017-03-26T02:30:00+01:00', $runDates);
        $this->assertNotContains('2017-03-26T02:30:00+02:00', $runDates);
        $this->assertNotContains('2017-03-26T03:30:00+01:00', $runDates);
    }

    public function testCalculatingIntervalWhenDstChangesBackward() {
        $schedule = new ScheduleWithTimezone('*/5 * * * *', 'Europe/Warsaw', ScheduleMode::MINUTELY());
        $runDates = array_map(
            self::formatPlannedTimestamp(),
            $this->planner->calculateScheduleExecutionsUntil($schedule, '2018-10-28 03:05', '2018-10-28 01:50')
        );
        $this->assertContains('2018-10-28T02:30:00+01:00', $runDates);
        $this->assertContains('2018-10-28T02:30:00+02:00', $runDates);
    }

    public function testCalculatingCronExpressionWhenDstChangesBackward() {
        $schedule = new ScheduleWithTimezone('30 2 * * *', 'Europe/Warsaw');
        $runDates = array_map(
            self::formatPlannedTimestamp(),
            $this->planner->calculateScheduleExecutionsUntil($schedule, '2024-10-29 00:00', '2024-10-27 00:00')
        );
        $this->assertContains('2024-10-27T02:30:00+02:00', $runDates);
        $this->assertNotContains('2024-10-28T02:30:00+02:00', $runDates);
        $this->assertContains('2024-10-28T02:30:00+01:00', $runDates);
    }

    public function testCalculatingIntervalWhenDstChangesBackwardInAmericaWinnipeg() {
        $schedule = new ScheduleWithTimezone('*/5 * * * *', 'America/Winnipeg', ScheduleMode::MINUTELY());
        $runDates = array_map(
            self::formatPlannedTimestamp(),
            $this->planner->calculateScheduleExecutionsUntil($schedule, '2018-11-04 02:05', '2018-11-04 00:50')
        );
        $this->assertContains('2018-11-04T01:30:00-05:00', $runDates);
        $this->assertContains('2018-11-04T01:30:00-06:00', $runDates);
    }

    public function testCalculatingCronExpressionWhenDstChangesBackwardInAmericaWinnipeg() {
        $schedule = new ScheduleWithTimezone('30 1 * * *', 'America/Winnipeg');
        $runDates = array_map(
            self::formatPlannedTimestamp(),
            $this->planner->calculateScheduleExecutionsUntil($schedule, '2018-11-05 00:00', '2018-11-03 00:00')
        );
        $this->assertContains('2018-11-03T01:30:00-05:00', $runDates);
        $this->assertContains('2018-11-04T01:30:00-05:00', $runDates);
        $this->assertContains('2018-11-04T01:30:00-06:00', $runDates);
        $this->assertContains('2018-11-05T01:30:00-06:00', $runDates);
    }

    public function testCalculatingRunForMelbourne() {
        $schedule = new ScheduleWithTimezone('*/5 * * * *', 'Australia/Melbourne');
        $runDates = array_map(
            self::formatPlannedTimestamp(),
            $this->planner->calculateScheduleExecutionsUntil($schedule, '2017-01-01 01:00', '2017-01-01 00:00')
        );
        $this->assertNotContains('2017-01-01T00:00:00+11:00', $runDates);
        $this->assertContains('2017-01-01T00:30:00+11:00', $runDates);
        $this->assertContains('2017-01-01T01:00:00+11:00', $runDates);
        $this->assertNotContains('2017-01-01T01:10:00+01:00', $runDates);
    }

    public function testCalculatingRunDatesUntilIfTheFirstOneIsLater() {
        $schedule = new ScheduleWithTimezone('23 11 5 12 * 2089');
        $runDates = array_map(
            self::formatPlannedTimestamp(),
            $this->planner->calculateScheduleExecutionsUntil($schedule, '2017-01-01 00:00')
        );
        $this->assertCount(1, $runDates);
        $this->assertContains('2089-12-05T11:23:00+01:00', $runDates);
    }

    public function testCalculatingRunDatesUntilDoesNotThrowAnErrorIfNoMoreDates() {
        $schedule = new ScheduleWithTimezone('23 11 5 12 * 2089');
        $runDates = array_map(
            self::formatPlannedTimestamp(),
            $this->planner->calculateScheduleExecutionsUntil($schedule, '2099-01-01 00:00')
        );
        $this->assertCount(1, $runDates);
        $this->assertContains('2089-12-05T11:23:00+01:00', $runDates);
    }

    public function testCalculatingManyDates() {
        $schedule = new ScheduleWithTimezone('*/5 * * * *');
        $dates = $this->planner->calculateScheduleExecutionsUntil($schedule, '+5 days');
        $this->assertGreaterThan(1000, count($dates));
    }

    // https://github.com/SUPLA/supla-cloud/issues/78
    public function testNextRunDateIsAlwaysOnTheNextDayForSunsetBasedSchedule() {
        $schedule = new ScheduleWithTimezone('SS0 * * * *', 'Europe/Warsaw');
        $nextRunDates = $this->planner->calculateScheduleExecutionsUntil($schedule, '2017-04-24 15:00:00', '2017-04-22 15:00:00');
        $formattedNextRunDates = array_map(self::formatPlannedTimestamp('Y-m-d H:i'), $nextRunDates);
        $this->assertEquals([
            '2017-04-22 19:48',
            '2017-04-23 19:50',
            '2017-04-24 19:51',
        ], $formattedNextRunDates);
    }

    public function testMinutesBasedSchedulesAreRelativeToStartTime() {
        $schedule = new ScheduleWithTimezone('*/10 * * * *', 'UTC', ScheduleMode::MINUTELY());
        $examples = [
            '2017-07-01 15:00:00' => '2017-07-01 15:10',
            '2017-07-01 15:01:00' => '2017-07-01 15:11',
            '2017-07-01 15:05:00' => '2017-07-01 15:15',
        ];
        foreach ($examples as $startDate => $expectedNextDate) {
            $startDate = new DateTime($startDate, $schedule->getUserTimezone());
            $nextExecution = $this->planner->calculateNextScheduleExecution($schedule, $startDate);
            $this->assertEquals($expectedNextDate, self::formatPlannedTimestamp('Y-m-d H:i')($nextExecution));
        }
    }

    public function testCalculatingIntervalDatesWithSpecificStartTime() {
        $schedule = new ScheduleWithTimezone('*/35 * * * *', 'Europe/Warsaw', ScheduleMode::MINUTELY());
        $runDates = array_map(
            self::formatPlannedTimestamp(),
            $this->planner->calculateScheduleExecutionsUntil($schedule, '2017-01-02 00:00', '2017-01-01 04:33')
        );
        $this->assertNotContains('2017-01-01T05:00:00+01:00', $runDates);
        $this->assertNotContains('2017-01-01T05:05:00+01:00', $runDates);
        $this->assertNotContains('2017-01-01T00:05:00+01:00', $runDates);
        $this->assertContains('2017-01-01T05:08:00+01:00', $runDates);
        $this->assertContains('2017-01-01T05:43:00+01:00', $runDates);
        $this->assertContains('2017-01-01T23:48:00+01:00', $runDates);
        $this->assertNotContains('2017-01-02T01:00:00+01:00', $runDates);
    }

    public function testPreservingTimezone() {
        $schedule = new ScheduleWithTimezone('*/5 * * * *', 'Australia/Sydney');
        $nextExecutions = $this->planner->calculateScheduleExecutionsUntil($schedule);
        $this->assertEquals($schedule->getUserTimezone(), $nextExecutions[0]->getPlannedTimestamp()->getTimezone());
    }

    /**
     * @dataProvider calculatingNextRunDateComplexConfigsProvider
     */
    public function testCalculatingNextRunDateForComplexConfigs($startDate, $config, $expectedNextRunDate, $timezone = 'Europe/Warsaw') {
        $schedule = new ScheduleWithTimezone($config, $timezone);
        $format = 'Y-m-d H:i';
        $formatter = CompositeSchedulePlannerTest::formatPlannedTimestamp($format);
        $startDate = DateTime::createFromFormat($format, $startDate, new DateTimeZone($timezone));
        $nextExecution = $this->planner->calculateNextScheduleExecution($schedule, $startDate);
        $this->assertEquals($expectedNextRunDate, $formatter($nextExecution));
    }

    public function calculatingNextRunDateComplexConfigsProvider() {
        $def = function ($crontab) {
            return ['crontab' => $crontab, 'action' => ['id' => ChannelFunctionAction::TURN_ON]];
        };
        return [
            ['2017-01-01 00:00', [$def('SR0 * * * *')], '2017-01-01 07:43'],
            ['2017-01-01 00:00', [$def('SR2 * * * *')], '2017-01-01 07:45'],
            ['2017-01-01 00:00', [$def('SR0 * * * 3')], '2017-01-04 07:42'],
            ['2021-05-23 00:00', [$def('10 10 * * 1'), $def('10 10 * * 2')], '2021-05-24 10:10'],
            ['2021-05-23 00:00', [$def('10 10 * * 2'), $def('10 10 * * 1')], '2021-05-24 10:10'],
            ['2021-05-23 00:00', [$def('SR0 * * * 1'), $def('10 10 * * 1')], '2021-05-24 04:26'],
            ['2021-05-24 04:30', [$def('SR0 * * * 1'), $def('10 10 * * 1')], '2021-05-24 10:10'],
        ];
    }

    public function testChoosingAppropriateAction() {
        $startDate = '2021-05-23 00:00';
        $expectedNextRunDate = '2021-05-24 04:26';
        $timezone = 'Europe/Warsaw';
        $config = [
            ['crontab' => '10 10 * * 1', 'action' => ['id' => ChannelFunctionAction::OPEN_PARTIALLY, 'param' => ['percentage' => 50]]],
            ['crontab' => 'SR0 * * * 1', 'action' => ['id' => ChannelFunctionAction::SET_RGBW_PARAMETERS, 'param' => ['hue' => 50]]],
        ];
        $schedule = new ScheduleWithTimezone($config, $timezone);
        $format = 'Y-m-d H:i';
        $formatter = CompositeSchedulePlannerTest::formatPlannedTimestamp($format);
        $startDate = DateTime::createFromFormat($format, $startDate, new DateTimeZone($timezone));
        $nextExecution = $this->planner->calculateNextScheduleExecution($schedule, $startDate);
        $this->assertEquals($expectedNextRunDate, $formatter($nextExecution));
        $this->assertEquals(ChannelFunctionAction::SET_RGBW_PARAMETERS, $nextExecution->getAction()->getId());
        $this->assertEquals(['hue' => 50], $nextExecution->getActionParam());
    }
}
