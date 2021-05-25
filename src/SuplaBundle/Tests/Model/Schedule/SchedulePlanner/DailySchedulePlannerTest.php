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
use InvalidArgumentException;
use PHPUnit_Framework_TestCase;
use SuplaBundle\Entity\EntityUtils;
use SuplaBundle\Enums\ChannelFunctionAction;
use SuplaBundle\Model\Schedule\SchedulePlanners\CompositeSchedulePlanner;
use SuplaBundle\Model\Schedule\SchedulePlanners\CronExpressionSchedulePlanner;
use SuplaBundle\Model\Schedule\SchedulePlanners\DailySchedulePlanner;
use SuplaBundle\Model\Schedule\SchedulePlanners\IntervalSchedulePlanner;
use SuplaBundle\Model\Schedule\SchedulePlanners\SunriseSunsetSchedulePlanner;

date_default_timezone_set('UTC');

class DailySchedulePlannerTest extends PHPUnit_Framework_TestCase {
    /** @var DailySchedulePlanner */
    private $planner;

    /** @before */
    public function init() {
        $this->planner = new DailySchedulePlanner();
        $compositePlanner = new CompositeSchedulePlanner([
            new IntervalSchedulePlanner(),
            new CronExpressionSchedulePlanner(),
            new SunriseSunsetSchedulePlanner(),
            $this->planner,
        ]);
        EntityUtils::setField($this->planner, 'compositePlanner', $compositePlanner);
    }

    /**
     * @dataProvider calculatingNextRunDateProvider
     */
    public function testCalculatingNextRunDate($startDate, $config, $expectedNextRunDate, $timezone = 'Europe/Warsaw') {
        $schedule = new ScheduleWithTimezone($config, $timezone);
        $format = 'Y-m-d H:i';
        $formatter = CompositeSchedulePlannerTest::formatPlannedTimestamp($format);
        $startDate = DateTime::createFromFormat($format, $startDate, new DateTimeZone($timezone));
        $nextExecution = $this->planner->calculateNextScheduleExecution($schedule, $startDate);
        $this->assertEquals($expectedNextRunDate, $formatter($nextExecution));
    }

    public function calculatingNextRunDateProvider() {
        $def = function ($crontab) {
            return ['crontab' => $crontab, 'action' => ['id' => ChannelFunctionAction::TURN_ON]];
        };
        return [
            ['2017-01-01 00:00', [$def('SR0 * * * *')], '2017-01-01 07:45'],
            ['2017-01-01 00:00', [$def('SR0 * * * 3')], '2017-01-04 07:45'],
            ['2021-05-23 00:00', [$def('10 10 * * 1'), $def('10 10 * * 2')], '2021-05-24 10:10'],
            ['2021-05-23 00:00', [$def('10 10 * * 2'), $def('10 10 * * 1')], '2021-05-24 10:10'],
            ['2021-05-23 00:00', [$def('SR0 * * * 1'), $def('10 10 * * 1')], '2021-05-24 04:30'],
            ['2021-05-24 04:30', [$def('SR0 * * * 1'), $def('10 10 * * 1')], '2021-05-24 10:10'],
        ];
    }

    public function testChoosingAppropriateAction() {
        $startDate = '2021-05-23 00:00';
        $expectedNextRunDate = '2021-05-24 04:30';
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

    /** @dataProvider configs */
    public function testSimplifyingConfig(array $config, array $expectedConfig) {
        $schedule = new ScheduleWithTimezone($config);
        $this->planner->validate($schedule);
        $this->assertEquals($expectedConfig, $schedule->getConfig());
    }

    public function configs(): array {
        return [
            [
                [['cron' => '10 10 * * *', 'action' => ['id' => ChannelFunctionAction::OPEN]]],
                [['cron' => '10 10 * * *', 'action' => ['id' => ChannelFunctionAction::OPEN]]],
            ],
            [
                [['cron' => '10 10 * * *', 'action' => ['id' => ChannelFunctionAction::OPEN], 'extra' => 'unicorn']],
                [['cron' => '10 10 * * *', 'action' => ['id' => ChannelFunctionAction::OPEN]]],
            ],
            [
                [['cron' => '10 10 * * *', 'action' => ['id' => ChannelFunctionAction::OPEN, 'extra' => 'unicorn']]],
                [['cron' => '10 10 * * *', 'action' => ['id' => ChannelFunctionAction::OPEN]]],
            ],
        ];
    }

    /** @dataProvider invalidConfigs */
    public function testInvalidConfig(array $config) {
        $this->expectException(InvalidArgumentException::class);
        $schedule = new ScheduleWithTimezone($config);
        $this->planner->validate($schedule);
    }

    public function invalidConfigs(): array {
        return [
            [[]],
            [[[]]],
            [[['unicorn' => 'blabla']]],
            [[['cron' => '10 10 * * *']]],
            [[['cron' => ['10 10 * * *'], 'action' => ['id' => ChannelFunctionAction::OPEN]]]],
        ];
    }
}
