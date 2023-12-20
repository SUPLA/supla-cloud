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

namespace SuplaBundle\Tests\Integration\Command\Cyclic;

use SuplaBundle\Entity\Main\AuditEntry;
use SuplaBundle\Enums\AuditedEvent;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelType;
use SuplaBundle\Enums\ScheduleActionExecutionResult;
use SuplaBundle\Enums\ScheduleMode;
use SuplaBundle\Model\Audit\Audit;
use SuplaBundle\Model\Schedule\ScheduleManager;
use SuplaBundle\Tests\Integration\IntegrationTestCase;
use SuplaBundle\Tests\Integration\Traits\SuplaApiHelper;

class DisableBrokenSchedulesCommandIntegrationTest extends IntegrationTestCase {
    use SuplaApiHelper;

    /** @var \SuplaBundle\Entity\Main\User */
    private $user;
    /** @var \SuplaBundle\Entity\Main\Schedule */
    private $schedule;

    private $resultSuccess = ScheduleActionExecutionResult::SUCCESS;
    private $resultFailure = ScheduleActionExecutionResult::DEVICE_UNREACHABLE;

    protected function initializeDatabaseForTests() {
        $this->user = $this->createConfirmedUser();
        $location = $this->createLocation($this->user);
        $device = $this->createDevice($location, [[ChannelType::RELAY, ChannelFunction::LIGHTSWITCH]]);
        $this->schedule = $this->createSchedule($device->getChannels()[0], '*/5 * * * *', ['mode' => ScheduleMode::MINUTELY]);
        self::$container->get(ScheduleManager::class)->generateScheduledExecutions($this->schedule, '+1day');
    }

    /** @small */
    public function testNotDisablingScheduleWithFutureExecutionsOnly() {
        $output = $this->executeCommand('supla:clean:disable-broken-schedules');
        $this->assertStringContainsString('Disabled 0 schedules', $output);
        $this->getEntityManager()->refresh($this->schedule);
        $this->assertTrue($this->schedule->getEnabled());
    }

    /** @small */
    public function testDoNotDisablingScheduleIfAtLeastOneSuccessful() {
        $this->getEntityManager()->getConnection()->executeQuery("UPDATE supla_scheduled_executions SET result=$this->resultFailure");
        $this->getEntityManager()->getConnection()->executeQuery(
            "UPDATE supla_scheduled_executions SET result=$this->resultSuccess WHERE id=100"
        );
        $output = $this->executeCommand('supla:clean:disable-broken-schedules');
        $this->assertStringContainsString('Disabled 0 schedules', $output);
    }

    /** @small */
    public function testDoNotDisablingScheduleIfAllExecutedWithoutConfirmation() {
        $this->getEntityManager()->getConnection()->executeQuery(
            'UPDATE supla_scheduled_executions SET result=' . ScheduleActionExecutionResult::EXECUTED_WITHOUT_CONFIRMATION
        );
        $output = $this->executeCommand('supla:clean:disable-broken-schedules');
        $this->assertStringContainsString('Disabled 0 schedules', $output);
    }

    public function testDisablingScheduleSuccessfulEntryLongTimeAgo() {
        $fiveWeeksAgo = date('Y-m-d H:i:s', strtotime('-5weeks'));
        $this->getEntityManager()->getConnection()->executeQuery("UPDATE supla_scheduled_executions SET result=$this->resultFailure");
        $this->getEntityManager()->getConnection()->executeQuery(
            "UPDATE supla_scheduled_executions SET result=$this->resultSuccess, planned_timestamp='$fiveWeeksAgo' WHERE id=100"
        );
        $output = $this->executeCommand('supla:clean:disable-broken-schedules');
        $this->assertStringContainsString('Disabled 1 schedules', $output);
    }

    public function testDisablingScheduleIfALotOfFailedExecutions() {
        $this->getEntityManager()->getConnection()->executeQuery("UPDATE supla_scheduled_executions SET result=$this->resultFailure");
        $output = $this->executeCommand('supla:clean:disable-broken-schedules');
        $this->assertStringContainsString('Disabled 1 schedules', $output);
        $this->getEntityManager()->refresh($this->schedule);
        $this->assertFalse($this->schedule->getEnabled());
    }

    /** @depends testDisablingScheduleIfALotOfFailedExecutions */
    public function testSavesDisablingBrokenScheduleInAUdit() {
        $entry = $this->getLatestAuditEntry();
        $this->assertEquals(AuditedEvent::SCHEDULE_BROKEN_DISABLED(), $entry->getEvent());
        $this->assertEquals($this->schedule->getId(), $entry->getIntParam());
    }

    private function getLatestAuditEntry(): AuditEntry {
        $entries = self::$container->get(Audit::class)->getRepository()->findAll();
        $this->assertGreaterThanOrEqual(1, count($entries));
        /** @var \SuplaBundle\Entity\Main\AuditEntry $entry */
        $entry = end($entries);
        return $entry;
    }
}
