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

use Doctrine\ORM\EntityManagerInterface;
use SuplaBundle\Entity\EntityUtils;
use SuplaBundle\Entity\MeasurementLogs\ElectricityMeterVoltageAberrationLogItem;
use SuplaBundle\Tests\Integration\IntegrationTestCase;
use SuplaBundle\Tests\Integration\Traits\MysqlUtcDate;
use Symfony\Component\Console\Tester\CommandTester;

/** @small */
class DeleteOldMeasurementLogsCommandIntegrationTest extends IntegrationTestCase {
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @before */
    protected function initializeEntityManager() {
        $this->entityManager = $this->getEntityManager('measurement_logs');
    }

    public function testDeletingOrphanedTemperatureLog() {
        $this->createEmVoltageLogItem('now');
        $this->createEmVoltageLogItem('-60 days');
        $this->createEmVoltageLogItem('-200 days');
        $logItems = $this->entityManager->getRepository(ElectricityMeterVoltageAberrationLogItem::class)->findAll();
        $this->assertCount(3, $logItems);
        $command = $this->application->find('supla:clean:old-measurement-logs');
        $commandTester = new CommandTester($command);
        $exitCode = $commandTester->execute([]);
        $this->assertEquals(0, $exitCode);
        $logItems = $this->entityManager->getRepository(ElectricityMeterVoltageAberrationLogItem::class)->findAll();
        $this->assertCount(2, $logItems);
    }

    private function createEmVoltageLogItem(string $date) {
        $logItem = new ElectricityMeterVoltageAberrationLogItem();
        EntityUtils::setField($logItem, 'channel_id', 2);
        EntityUtils::setField($logItem, 'date', MysqlUtcDate::toString('@' . strtotime($date)));
        $state = [
            'phaseNo' => 1, 'measurementTimeSec' => 600,
            'countAbove' => 2, 'countBelow' => 3, 'countTotal' => 5,
            'secAbove' => 0, 'secBelow' => 3, 'maxSecAbove' => 2, 'maxSecBelow' => 3,
            'minVoltage' => 100, 'maxVoltage' => 230, 'avgVoltage' => 200,
        ];
        foreach ($state as $stateName => $value) {
            EntityUtils::setField($logItem, $stateName, $state[$stateName]);
        }
        $this->entityManager->persist($logItem);
        $this->entityManager->flush();
    }
}
