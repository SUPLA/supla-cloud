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

namespace SuplaBundle\Tests\Integration\Migrations;

use SuplaBundle\Entity\EntityUtils;
use SuplaBundle\Entity\Main\GateClosingRule;
use SuplaBundle\Entity\MeasurementLogs\ElectricityMeterVoltageAberrationLogItem;
use SuplaBundle\Tests\Integration\IntegrationTestCase;
use SuplaBundle\Tests\Integration\Traits\UserFixtures;

/** @small */
class SuplaServerSqlProceduresIntegrationTest extends IntegrationTestCase {
    use UserFixtures;

    protected function initializeDatabaseForTests() {
//        $this->initializeDatabaseWithMigrations();
        $user = $this->createConfirmedUser();
        $this->createDeviceFull($user->getLocations()[0]);
    }

    public function testMarkGateClosedWhenNoConfig() {
        $query = 'CALL supla_mark_gate_closed(1)';
        $this->getEntityManager()->getConnection()->executeQuery($query);
        $state = $this->getEntityManager()->getRepository(GateClosingRule::class)->find(1);
        $this->assertNull($state);
    }

    public function testMarkGateClosed() {
        $this->getEntityManager()->getConnection()->executeQuery(
            'INSERT INTO supla_auto_gate_closing (channel_id, user_id, max_time_open, last_seen_open) VALUES(2, 1, 10, NOW())'
        );
        $query = 'CALL supla_mark_gate_closed(2)';
        $this->getEntityManager()->getConnection()->executeQuery($query);
        /** @var GateClosingRule $state */
        $state = $this->getEntityManager()->getRepository(GateClosingRule::class)->find(2);
        $this->assertNotNull($state);
        $this->assertEquals(10, $state->getMaxTimeOpen());
        $this->assertNull(EntityUtils::getField($state, 'lastSeenOpen'));
    }

    /** @depends testMarkGateClosed */
    public function testSetClosingAttempt() {
        $query = 'CALL supla_set_closing_attempt(2)';
        $this->getEntityManager()->getConnection()->executeQuery($query);
        /** @var GateClosingRule $state */
        $state = $this->getEntityManager()->getRepository(GateClosingRule::class)->find(2);
        /** @var \DateTime $closingAttemptDate */
        $closingAttemptDate = EntityUtils::getField($state, 'closingAttempt');
        $this->assertNotNull($closingAttemptDate);
        $this->assertGreaterThanOrEqual(time(), $closingAttemptDate->getTimestamp());
    }

    public function testSuplaAddEmVoltageLogItem() {
        $parameters = ['"2022-10-26 16:09:00"', 1, 2, 3, 4, 5, 6, 7, 8, 9, 11.5, 12.5, 13.5, 14];
        $query = 'CALL supla_add_em_voltage_aberration_log_item(' . implode(', ', $parameters) . ')';
        $this->getEntityManager('measurement_logs')->getConnection()->executeQuery($query);
        $logItems = $this->getEntityManager('measurement_logs')->getRepository(ElectricityMeterVoltageAberrationLogItem::class)->findAll();
        $this->assertCount(1, $logItems);
        /** @var \SuplaBundle\Entity\MeasurementLogs\ElectricityMeterVoltageAberrationLogItem $logItem */
        $logItem = $logItems[0];
        $this->assertNotNull($logItem);
        $this->assertEquals(1, $logItem->getChannelId());
        $this->assertEquals(2, EntityUtils::getField($logItem, 'phaseNo'));
        $this->assertEquals(6, EntityUtils::getField($logItem, 'secAbove'));
        $this->assertEquals(13.5, EntityUtils::getField($logItem, 'avgVoltage'));
        $this->assertEquals('2022-10-26 16:09:00', EntityUtils::getField($logItem, 'date'));
    }
}
