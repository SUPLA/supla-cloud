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

namespace SuplaBundle\Tests\Integration\Model\Schedule;

use DateTime;
use InvalidArgumentException;
use SuplaBundle\Entity\Main\Schedule;
use SuplaBundle\Entity\Main\ScheduledExecution;
use SuplaBundle\Enums\ChannelFunctionAction;
use SuplaBundle\Enums\ScheduleMode;
use SuplaBundle\Model\Schedule\ScheduleManager;
use SuplaBundle\Tests\Integration\IntegrationTestCase;
use SuplaBundle\Tests\Integration\Traits\TestTimeProvider;
use SuplaBundle\Tests\Integration\Traits\UserFixtures;

/** @small */
class ScheduleManagerIntegrationTest extends IntegrationTestCase {
    use UserFixtures;

    /** @var \SuplaBundle\Entity\Main\IODeviceChannel */
    private $channel;

    protected function initializeDatabaseForTests() {
        $user = $this->createConfirmedUser();
        $location = $this->createLocation($user);
        $sonoff = $this->createDeviceSonoff($location);
        $this->channel = $sonoff->getChannels()[0];
    }

    public function testCalculateNextRunDateForOnceSchedule() {
        $schedule = $this->createSchedule($this->channel, '0 0 1 1 * 2088');
        $this->assertNotNull($schedule->getNextCalculationDate());
        $executions = $this->getDoctrine()->getRepository(ScheduledExecution::class)->findBy(['schedule' => $schedule]);
        $this->assertCount(1, $executions);
        /** @var ScheduledExecution $execution */
        $execution = current($executions);
        $this->assertEquals($schedule->getId(), $execution->getSchedule()->getId());
        $this->assertEquals(new DateTime('2088-01-01 00:00:00'), $execution->getPlannedTimestamp());
    }

    public function testDoesNotGenerateRunDateForSchedulesWithPastEndDate() {
        $schedule = $this->createSchedule($this->channel, '*/5 * * * *', [
            'mode' => ScheduleMode::MINUTELY,
            'dateEnd' => (new DateTime('2018-01-01 00:00:00'))->format(DateTime::ATOM),
        ]);
        $executions = $this->getDoctrine()->getRepository(ScheduledExecution::class)->findBy(['schedule' => $schedule]);
        $this->assertEmpty($executions);
    }

    public function testDisablesScheduleIfNoMoreExecutions() {
        TestTimeProvider::setTime('2018-01-01 00:00:00');
        $schedule = $this->createSchedule($this->channel, '*/5 * * * *', [
            'mode' => ScheduleMode::MINUTELY,
            'dateStart' => (new DateTime('2018-01-01 00:00:00'))->format(DateTime::ATOM),
            'dateEnd' => (new DateTime('2018-01-01 01:00:00'))->format(DateTime::ATOM),
        ]);
        self::$container->get(ScheduleManager::class)->generateScheduledExecutions($schedule);
        $this->assertTrue($schedule->getEnabled());
        TestTimeProvider::setTime('2018-01-01 01:00:01');
        self::$container->get(ScheduleManager::class)->generateScheduledExecutions($schedule);
        $this->assertFalse($schedule->getEnabled());
    }

    public function testDoesNotDisableIfThereArePendingExecutionsToExecute() {
        TestTimeProvider::setTime('2018-01-01 00:00:00');
        $schedule = $this->createSchedule($this->channel, '*/5 * * * *', [
            'mode' => ScheduleMode::MINUTELY,
            'dateStart' => (new DateTime('2018-01-01 00:00:00'))->format(DateTime::ATOM),
            'dateEnd' => (new DateTime('2018-01-01 01:00:00'))->format(DateTime::ATOM),
        ]);
        self::$container->get(ScheduleManager::class)->generateScheduledExecutions($schedule);
        self::$container->get(ScheduleManager::class)->generateScheduledExecutions($schedule);
        $this->assertTrue($schedule->getEnabled());
    }

    public function testDoesNotDisableFutureOnceSchedule() {
        $schedule = $this->createSchedule($this->channel, '0 0 1 1 * 2088', ['mode' => ScheduleMode::ONCE]);
        self::$container->get(ScheduleManager::class)->generateScheduledExecutions($schedule);
        self::$container->get(ScheduleManager::class)->generateScheduledExecutions($schedule);
        $this->assertTrue($schedule->getEnabled());
    }

    /** @dataProvider exampleConfigs */
    public function testValidatingConfig(array $config, bool $expectValid = false) {
        $schedule = new Schedule($this->channel->getUser(), [
            'subject' => $this->channel,
            'mode' => ScheduleMode::ONCE,
            'config' => $config,
        ]);
        if (!$expectValid) {
            $this->expectException(InvalidArgumentException::class);
        }
        self::$container->get(ScheduleManager::class)->validateSchedule($schedule);
        $this->assertTrue(true);
    }

    public function exampleConfigs(): array {
        return [
            [[]],
            [[[]]],
            [[['unicorn' => 'blabla']]],
            [[['crontab' => '10 10 * * *']]],
            [[['crontab' => ['10 10 * * *'], 'action' => ['id' => ChannelFunctionAction::OPEN]]]],
            [[['crontab' => '10 10 * * 1-2', 'action' => ['id' => ChannelFunctionAction::OPEN]]]], // invalid action
            [[['crontab' => '10 10 * * 1,a', 'action' => ['id' => ChannelFunctionAction::OPEN]]]],
            [[['crontab' => '10 10 * * 1-2', 'action' => ['id' => ChannelFunctionAction::TURN_ON, 'extra' => true]]], true],
            [[['crontab' => '10 10 * * 1-2', 'action' => ['id' => ChannelFunctionAction::TURN_ON], 'extra' => true]]],
            [[['crontab' => '10 10 * * 1-2', 'action' => ['id' => ChannelFunctionAction::TURN_ON]]], true],
        ];
    }
}
