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

namespace SuplaBundle\Tests\Integration\MeasurementLogs;

use SuplaBundle\Entity\MeasurementLogs\EnergyPriceLogItem;
use SuplaBundle\Supla\SuplaAutodiscoverMock;
use SuplaBundle\Tests\Integration\IntegrationTestCase;

/** @small */
class EnergyPriceForecastFetchCommandIntegrationTest extends IntegrationTestCase {
    public function testFetchingData() {
        // @codingStandardsIgnoreStart
        SuplaAutodiscoverMock::mockResponse('energy-price-forecast', [
            ['dateFrom' => '2025-06-12T00:00:00+02:00', 'dateTo' => '2025-06-12T00:14:59+02:00', 'rce' => 440.8, 'fixing1' => 436.2],
            ['dateFrom' => '2025-06-12T00:15:00+02:00', 'dateTo' => '2025-06-12T00:29:59+02:00', 'rce' => 440.8],
            ['dateFrom' => '2025-06-12T00:30:00+02:00', 'dateTo' => '2025-06-12T00:44:59+02:00', 'rce' => 440.8],
        ]);
        // @codingStandardsIgnoreEnd
        $output = $this->executeCommand('supla:cyclic:energy-price-forecast-fetch');
        $em = $this->getEntityManager('measurement_logs');
        $logsRepo = $em->getRepository(EnergyPriceLogItem::class);
        $this->assertEquals(3, $logsRepo->count([]));
        /** @var EnergyPriceLogItem $firstLog */
        $firstLog = $logsRepo->find('2025-06-11 22:00:00');
        $this->assertNotNull($firstLog);
        $this->assertEquals(440.8, $firstLog->getRce());
        $this->assertEquals(436.2, $firstLog->getFixing1());
        $this->assertNull($firstLog->getFixing2());
    }

    /** @depends testFetchingData */
    public function testFetchingDataSecondTimeCorrects() {
        // @codingStandardsIgnoreStart
        SuplaAutodiscoverMock::mockResponse('energy-price-forecast', [
            ['dateFrom' => '2025-06-12T00:00:00+02:00', 'dateTo' => '2025-06-12T00:14:59+02:00', 'rce' => 441.8, 'fixing1' => 436.2, 'fixing2' => 448.91],
            ['dateFrom' => '2025-06-12T00:15:00+02:00', 'dateTo' => '2025-06-12T00:29:59+02:00', 'rce' => 440.8, 'fixing1' => 436.2, 'fixing2' => 448.91],
            ['dateFrom' => '2025-06-12T00:30:00+02:00', 'dateTo' => '2025-06-12T00:44:59+02:00', 'rce' => 440.8],
        ]);
        // @codingStandardsIgnoreEnd
        $output = $this->executeCommand('supla:cyclic:energy-price-forecast-fetch');
        $em = $this->getEntityManager('measurement_logs');
        $logsRepo = $em->getRepository(EnergyPriceLogItem::class);
        $this->assertEquals(3, $logsRepo->count([]));
        /** @var EnergyPriceLogItem $firstLog */
        $firstLog = $logsRepo->find('2025-06-11 22:00:00');
        $this->assertNotNull($firstLog);
        $this->assertEquals(441.8, $firstLog->getRce());
        $this->assertEquals(436.2, $firstLog->getFixing1());
        $this->assertEquals(448.91, $firstLog->getFixing2());
    }
}
