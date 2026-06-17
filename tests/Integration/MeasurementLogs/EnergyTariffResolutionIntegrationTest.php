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

namespace App\Tests\Integration\MeasurementLogs;

use App\Entity\MeasurementLogs\EnergyTariff;
use App\Entity\MeasurementLogs\EnergyTariffResolvedZone;
use App\Model\MeasurementLogsEntityManagerProvider;
use App\Tests\Integration\IntegrationTestCase;
use App\Tests\Integration\Traits\TestTimeProvider;
use PHPUnit\Framework\Attributes\Small;

#[Small]
class EnergyTariffResolutionIntegrationTest extends IntegrationTestCase {
    public function testMaterializingG11TariffZones(): void {
        $tariff = $this->createTariffFromFixture('PL_G11', 'Polish G11', 'g11-zone-profile.json');

        TestTimeProvider::setTime('2026-01-01 00:00:00 UTC');
        $this->executeCommand('supla:cyclic:resolve-energy-tariffs --months-ahead=1');

        $zones = $this->fetchResolvedZones($tariff);

        $this->assertCount(1, $zones);
        $this->assertResolvedZone($zones[0], 'ALL_DAY', '2026-01-01 00:00:00', '2026-02-01 00:00:00');
    }

    public function testMaterializingG12TariffZones(): void {
        $tariff = $this->createTariffFromFixture('PL_G12', 'Polish G12', 'g12-zone-profile.json');

        TestTimeProvider::setTime('2026-01-01 00:00:00 UTC');
        $this->executeCommand('supla:cyclic:resolve-energy-tariffs --months-ahead=1');

        $zones = $this->fetchResolvedZones($tariff);

        $this->assertCount(63, $zones);
        $this->assertResolvedZone($zones[0], 'NIGHT', '2026-01-01 00:00:00', '2026-01-01 06:00:00');
        $this->assertResolvedZone($zones[1], 'DAY', '2026-01-01 06:00:00', '2026-01-01 22:00:00');
        $this->assertResolvedZone($zones[2], 'NIGHT', '2026-01-01 22:00:00', '2026-01-02 06:00:00');
        $this->assertResolvedZone($zones[62], 'NIGHT', '2026-01-31 22:00:00', '2026-02-01 00:00:00');
    }

    /**
     * @return EnergyTariffResolvedZone[]
     */
    private function fetchResolvedZones(EnergyTariff $tariff): array {
        $logsEm = self::getContainer()->get(MeasurementLogsEntityManagerProvider::class)->get();

        return $logsEm->createQueryBuilder()
            ->select('z')
            ->from(EnergyTariffResolvedZone::class, 'z')
            ->where('z.tariffId = :tariffId')
            ->setParameter('tariffId', $tariff->getId())
            ->orderBy('z.periodStart', 'ASC')
            ->getQuery()
            ->getResult();
    }

    private function createTariffFromFixture(string $code, string $name, string $fixtureName): EnergyTariff {
        $logsEm = self::getContainer()->get(MeasurementLogsEntityManagerProvider::class)->get();

        $tariff = new EnergyTariff();
        $tariff->setCode($code);
        $tariff->setName($name);
        $tariff->setConfig(json_decode(file_get_contents(__DIR__ . '/tariffs/' . $fixtureName), true));
        $logsEm->persist($tariff);
        $logsEm->flush();

        return $tariff;
    }

    private function assertResolvedZone(EnergyTariffResolvedZone $zone, string $zoneCode, string $periodStart, string $periodEnd): void {
        $this->assertSame($zoneCode, $zone->getZoneCode());
        $this->assertSame($periodStart, $zone->getPeriodStart()->format('Y-m-d H:i:s'));
        $this->assertSame($periodEnd, $zone->getPeriodEnd()->format('Y-m-d H:i:s'));
    }
}
