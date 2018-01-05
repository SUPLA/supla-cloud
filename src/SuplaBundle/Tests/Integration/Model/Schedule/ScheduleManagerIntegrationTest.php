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

namespace SuplaBundle\Tests\Integration\Model;

use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Entity\Schedule;
use SuplaBundle\Entity\ScheduledExecution;
use SuplaBundle\Enums\ScheduleAction;
use SuplaBundle\Enums\ScheduleMode;
use SuplaBundle\Model\Schedule\ScheduleManager;
use SuplaBundle\Tests\Integration\IntegrationTestCase;
use SuplaBundle\Tests\Integration\Traits\TestTimeProvider;
use SuplaBundle\Tests\Integration\Traits\UserFixtures;

class ScheduleManagerIntegrationTest extends IntegrationTestCase {
    use UserFixtures;

    /** @var ScheduleManager */
    private $scheduleManager;

    /** @var IODeviceChannel */
    private $channel;

    /** @var TestTimeProvider */
    private $timeProvider;

    protected function setUp() {
        $this->scheduleManager = $this->container->get('schedule_manager');
        $user = $this->createConfirmedUser();
        $location = $this->createLocation($user);
        $sonoff = $this->createDeviceSonoff($location);
        $this->channel = $sonoff->getChannels()[0];
        $timeProvider = new TestTimeProvider();
        $setter = function () use ($timeProvider) {
            $this->timeProvider = $timeProvider;
        };
        $setter->call($this->scheduleManager);
        $this->timeProvider = $timeProvider;
    }

    public function testCreatedScheduleIsEmpty() {
        $schedule = $this->createSchedule([
            'timeExpression' => '0 0 1 1 * 2088',
            'scheduleMode' => ScheduleMode::ONCE,
        ]);
        $this->assertGreaterThan(0, $schedule->getId());
        $this->assertNull($schedule->getNextCalculationDate());
        $executions = $this->getDoctrine()->getRepository(ScheduledExecution::class)->findAll();
        $this->assertEmpty($executions);
    }

    public function testCalculateNextRunDateForOnceSchedule() {
        $schedule = $this->createSchedule([
            'timeExpression' => '0 0 1 1 * 2088',
            'scheduleMode' => ScheduleMode::ONCE,
        ]);
        $this->scheduleManager->generateScheduledExecutions($schedule);
        $this->assertNotNull($schedule->getNextCalculationDate());
        $executions = $this->getDoctrine()->getRepository(ScheduledExecution::class)->findAll();
        $this->assertCount(1, $executions);
        /** @var ScheduledExecution $execution */
        $execution = current($executions);
        $this->assertEquals($schedule->getId(), $execution->getSchedule()->getId());
        $this->assertEquals(new \DateTime('2088-01-01 00:00:00'), $execution->getPlannedTimestamp());
    }

    public function testDoesNotGenerateRunDateForSchedulesWithPastEndDate() {
        $schedule = $this->createSchedule([
            'timeExpression' => '*/5 * * * *',
            'scheduleMode' => ScheduleMode::MINUTELY,
            'dateEnd' => (new \DateTime('2018-01-01 00:00:00'))->format(\DateTime::ATOM),
        ]);
        $this->scheduleManager->generateScheduledExecutions($schedule);
        $executions = $this->getDoctrine()->getRepository(ScheduledExecution::class)->findAll();
        $this->assertEmpty($executions);
    }

    public function testDisablesScheduleIfNoMoreExecutions() {
        $schedule = $this->createSchedule([
            'timeExpression' => '*/5 * * * *',
            'scheduleMode' => ScheduleMode::MINUTELY,
            'dateStart' => (new \DateTime('2018-01-01 00:00:00'))->format(\DateTime::ATOM),
            'dateEnd' => (new \DateTime('2018-01-01 01:00:00'))->format(\DateTime::ATOM),
        ]);
        $this->timeProvider->setTimestamp('2018-01-01 00:00:00');
        $this->scheduleManager->generateScheduledExecutions($schedule);
        $this->assertTrue($schedule->getEnabled());
        $this->timeProvider->setTimestamp('2018-01-01 01:00:01');
        $this->scheduleManager->generateScheduledExecutions($schedule);
        $this->assertFalse($schedule->getEnabled());
    }

    public function testDoesNotDisableIfThereArePendingExecutionsToExecute() {
        $schedule = $this->createSchedule([
            'timeExpression' => '*/5 * * * *',
            'scheduleMode' => ScheduleMode::MINUTELY,
            'dateStart' => (new \DateTime('2018-01-01 00:00:00'))->format(\DateTime::ATOM),
            'dateEnd' => (new \DateTime('2018-01-01 01:00:00'))->format(\DateTime::ATOM),
        ]);
        $this->timeProvider->setTimestamp('2018-01-01 00:00:00');
        $this->scheduleManager->generateScheduledExecutions($schedule);
        $this->scheduleManager->generateScheduledExecutions($schedule);
        $this->assertTrue($schedule->getEnabled());
    }

    public function testDoesNotDisableFutureOnceSchedule() {
        $schedule = $this->createSchedule([
            'timeExpression' => '0 0 1 1 * 2088',
            'scheduleMode' => ScheduleMode::MINUTELY,
        ]);
        $this->scheduleManager->generateScheduledExecutions($schedule);
        $this->scheduleManager->generateScheduledExecutions($schedule);
        $this->assertTrue($schedule->getEnabled());
    }

    private function createSchedule($data): Schedule {
        $schedule = new Schedule($this->channel->getIoDevice()->getUser(), array_merge([
            'action' => ScheduleAction::TURN_ON,
            'channel' => $this->channel,
        ], $data));
        $em = $this->getDoctrine()->getManager();
        $em->persist($schedule);
        $em->flush();
        return $schedule;
    }
}
