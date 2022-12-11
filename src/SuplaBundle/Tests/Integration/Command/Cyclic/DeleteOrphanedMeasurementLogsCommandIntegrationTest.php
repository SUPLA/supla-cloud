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

use SuplaBundle\Entity\EntityUtils;
use SuplaBundle\Entity\MeasurementLogs\TemperatureLogItem;
use SuplaBundle\Tests\Integration\IntegrationTestCase;
use SuplaBundle\Tests\Integration\Traits\MysqlUtcDate;
use Symfony\Component\Console\Tester\CommandTester;

/** @small */
class DeleteOrphanedMeasurementLogsCommandIntegrationTest extends IntegrationTestCase {
    public function testDeletingOrphanedTemperatureLog() {
        $logItem = new TemperatureLogItem();
        EntityUtils::setField($logItem, 'channel_id', 2);
        EntityUtils::setField($logItem, 'date', MysqlUtcDate::toString(new \DateTime()));
        EntityUtils::setField($logItem, 'temperature', 20);
        $entityManager = $this->getEntityManager('measurement_logs');
        $entityManager->persist($logItem);
        $entityManager->flush();
        $command = $this->application->find('supla:clean:orphaned-measurement-logs');
        $commandTester = new CommandTester($command);
        $exitCode = $commandTester->execute([]);
        $this->assertEquals(0, $exitCode);
        $logItems = $entityManager->getRepository(TemperatureLogItem::class)->findAll();
        $this->assertEmpty($logItems);
    }
}
