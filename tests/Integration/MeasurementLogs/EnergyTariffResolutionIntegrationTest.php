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
use App\Entity\MeasurementLogs\EnergyTariffHoliday;
use App\Entity\MeasurementLogs\EnergyTariffResolvedZone;
use App\Model\MeasurementLogsEntityManagerProvider;
use App\Tests\Integration\IntegrationTestCase;
use App\Tests\Integration\Traits\TestTimeProvider;
use PHPUnit\Framework\Attributes\Small;

#[Small]
class EnergyTariffResolutionIntegrationTest extends IntegrationTestCase {
    private const DEFINITIONS_FILE = __DIR__ . '/../../../src/DataFixtures/tariff-definitions.json';
    private const WINTER_START_DATE = '2025-12-01 00:00:00';
    private const SUMMER_START_DATE = '2026-06-01 00:00:00';

    public function testGeneratingWarsawTariffHolidays(): void {
        $this->createTariffFromFixture('PL_G13_TAURON', 'Polish G13 Tauron');

        TestTimeProvider::setTime('2026-01-01 00:00:00 UTC');
        $this->executeCommand('supla:cyclic:generate-energy-tariff-holidays --years-ahead=1');

        $holidays = $this->fetchHolidays('Europe/Warsaw');

        $this->assertContains('2018-01-01', $holidays);
        $this->assertContains('2026-01-01', $holidays);
        $this->assertContains('2026-01-06', $holidays);
        $this->assertContains('2026-04-05', $holidays);
        $this->assertContains('2026-04-06', $holidays);
        $this->assertContains('2026-06-04', $holidays);
    }

    public function testMaterializingG11TariffZones(): void {
        $tariff = $this->createTariffFromFixture('PL_G11', 'Polish G11');

        TestTimeProvider::setTime('2026-01-01 00:00:00 UTC');
        $this->executeCommand('supla:cyclic:generate-energy-tariff-holidays --years-ahead=1');
        $this->executeCommand(sprintf(
            'supla:cyclic:resolve-energy-tariffs --months-ahead=1 --start-date="%s"',
            self::WINTER_START_DATE
        ));

        $zones = $this->fetchResolvedZones($tariff);

        $this->assertCount(1, $zones);
        $this->assertResolvedZone($zones[0], 'ALL_DAY', '2025-11-30 23:00:00', '2026-01-31 23:00:00');
    }

    public function testMaterializingG12TariffZones(): void {
        $tariff = $this->createTariffFromFixture('PL_G12', 'Polish G12');

        TestTimeProvider::setTime('2026-01-01 00:00:00 UTC');
        $this->executeCommand('supla:cyclic:generate-energy-tariff-holidays --years-ahead=1');
        $this->executeCommand(sprintf(
            'supla:cyclic:resolve-energy-tariffs --months-ahead=1 --start-date="%s"',
            self::WINTER_START_DATE
        ));

        $zones = $this->fetchResolvedZones($tariff, '2025-12-31 23:00:00');

        $this->assertCount(63, $zones);
        $this->assertResolvedZone($zones[0], 'NIGHT', '2025-12-31 21:00:00', '2026-01-01 05:00:00');
        $this->assertResolvedZone($zones[1], 'DAY', '2026-01-01 05:00:00', '2026-01-01 21:00:00');
        $this->assertResolvedZone($zones[2], 'NIGHT', '2026-01-01 21:00:00', '2026-01-02 05:00:00');
        $this->assertResolvedZone($zones[62], 'NIGHT', '2026-01-31 21:00:00', '2026-01-31 23:00:00');
    }

    public function testMaterializingG13WinterTariffZones(): void {
        $tariff = $this->createTariffFromFixture('PL_G13_TAURON', 'Polish G13 Tauron');

        TestTimeProvider::setTime('2026-01-01 00:00:00 UTC');
        $this->executeCommand('supla:cyclic:generate-energy-tariff-holidays --years-ahead=1');
        $this->executeCommand(sprintf(
            'supla:cyclic:resolve-energy-tariffs --months-ahead=1 --start-date="%s"',
            self::WINTER_START_DATE
        ));

        $zones = $this->fetchResolvedZones($tariff, '2025-12-31 23:00:00');

        $this->assertResolvedZone($zones[0], 'OFF_PEAK', '2025-12-31 20:00:00', '2026-01-02 06:00:00');
        $this->assertResolvedZone($zones[1], 'MORNING_PEAK', '2026-01-02 06:00:00', '2026-01-02 12:00:00');
        $this->assertResolvedZone($zones[2], 'OFF_PEAK', '2026-01-02 12:00:00', '2026-01-02 15:00:00');
        $this->assertResolvedZone($zones[3], 'AFTERNOON_PEAK', '2026-01-02 15:00:00', '2026-01-02 20:00:00');
        $this->assertResolvedZone($zones[4], 'OFF_PEAK', '2026-01-02 20:00:00', '2026-01-05 06:00:00');
        $this->assertResolvedZone($zones[8], 'OFF_PEAK', '2026-01-05 20:00:00', '2026-01-07 06:00:00');
    }

    public function testMaterializingG13SummerTariffZones(): void {
        $tariff = $this->createTariffFromFixture('PL_G13_TAURON', 'Polish G13 Tauron');

        TestTimeProvider::setTime('2026-07-01 00:00:00 UTC');
        $this->executeCommand('supla:cyclic:generate-energy-tariff-holidays --years-ahead=1');
        $this->executeCommand(sprintf(
            'supla:cyclic:resolve-energy-tariffs --months-ahead=1 --start-date="%s"',
            self::SUMMER_START_DATE
        ));

        $zones = $this->fetchResolvedZones($tariff, '2026-06-30 22:00:00');

        $this->assertResolvedZone($zones[0], 'OFF_PEAK', '2026-06-30 20:00:00', '2026-07-01 05:00:00');
        $this->assertResolvedZone($zones[1], 'MORNING_PEAK', '2026-07-01 05:00:00', '2026-07-01 11:00:00');
        $this->assertResolvedZone($zones[2], 'OFF_PEAK', '2026-07-01 11:00:00', '2026-07-01 17:00:00');
        $this->assertResolvedZone($zones[3], 'AFTERNOON_PEAK', '2026-07-01 17:00:00', '2026-07-01 20:00:00');
        $this->assertResolvedZone($zones[4], 'OFF_PEAK', '2026-07-01 20:00:00', '2026-07-02 05:00:00');
    }

    public function testContinuesFromLastResolvedZoneByDefault(): void {
        $tariff = $this->createTariffFromFixture('PL_G11', 'Polish G11');

        TestTimeProvider::setTime('2026-01-01 00:00:00 UTC');
        $this->executeCommand('supla:cyclic:generate-energy-tariff-holidays --years-ahead=1');
        $this->executeCommand(sprintf(
            'supla:cyclic:resolve-energy-tariffs --months-ahead=1 --start-date="%s"',
            self::WINTER_START_DATE
        ));

        TestTimeProvider::setTime('2026-02-01 00:00:00 UTC');
        $this->executeCommand(sprintf(
            'supla:cyclic:resolve-energy-tariffs --months-ahead=1 --start-date="%s"',
            self::WINTER_START_DATE
        ));

        $zones = $this->fetchResolvedZones($tariff);

        $this->assertCount(2, $zones);
        $this->assertResolvedZone($zones[0], 'ALL_DAY', '2025-11-30 23:00:00', '2026-01-31 23:00:00');
        $this->assertResolvedZone($zones[1], 'ALL_DAY', '2026-01-31 23:00:00', '2026-02-28 23:00:00');
    }

    public function testRecalculateStartsAgainFromTheBeginning(): void {
        $tariff = $this->createTariffFromFixture('PL_G11', 'Polish G11');

        TestTimeProvider::setTime('2026-01-01 00:00:00 UTC');
        $this->executeCommand('supla:cyclic:generate-energy-tariff-holidays --years-ahead=1');
        $this->executeCommand(sprintf(
            'supla:cyclic:resolve-energy-tariffs --months-ahead=1 --start-date="%s"',
            self::WINTER_START_DATE
        ));

        TestTimeProvider::setTime('2026-02-01 00:00:00 UTC');
        $this->executeCommand(sprintf(
            'supla:cyclic:resolve-energy-tariffs --months-ahead=1 --start-date="%s"',
            self::WINTER_START_DATE
        ));
        $this->executeCommand(sprintf(
            'supla:cyclic:resolve-energy-tariffs --months-ahead=1 --recalculate --start-date="%s"',
            self::WINTER_START_DATE
        ));

        $zones = $this->fetchResolvedZones($tariff);

        $this->assertCount(1, $zones);
        $this->assertResolvedZone($zones[0], 'ALL_DAY', '2025-11-30 23:00:00', '2026-02-28 23:00:00');
    }

    public function testCanResolveOnlySelectedTariff(): void {
        $resolvedTariff = $this->createTariffFromFixture('PL_G11', 'Polish G11');
        $skippedTariff = $this->createTariffFromFixture('PL_G12', 'Polish G12');

        TestTimeProvider::setTime('2026-01-01 00:00:00 UTC');
        $this->executeCommand('supla:cyclic:generate-energy-tariff-holidays --years-ahead=1');
        $this->executeCommand(sprintf(
            'supla:cyclic:resolve-energy-tariffs --months-ahead=1 --tariff-code=PL_G11 --start-date="%s"',
            self::WINTER_START_DATE
        ));

        $this->assertNotEmpty($this->fetchResolvedZones($resolvedTariff));
        $this->assertCount(0, $this->fetchResolvedZones($skippedTariff));
    }

    /**
     * @return EnergyTariffResolvedZone[]
     */
    private function fetchResolvedZones(EnergyTariff $tariff, ?string $from = null): array {
        $logsEm = self::getContainer()->get(MeasurementLogsEntityManagerProvider::class)->get();

        $queryBuilder = $logsEm->createQueryBuilder()
            ->select('z')
            ->from(EnergyTariffResolvedZone::class, 'z')
            ->where('z.tariffId = :tariffId')
            ->setParameter('tariffId', $tariff->getId())
            ->orderBy('z.periodStart', 'ASC');

        if ($from !== null) {
            $queryBuilder
                ->andWhere('z.periodEnd > :from')
                ->setParameter('from', new \DateTime($from, new \DateTimeZone('UTC')));
        }

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @return string[]
     */
    private function fetchHolidays(string $timezone): array {
        $logsEm = self::getContainer()->get(MeasurementLogsEntityManagerProvider::class)->get();

        return array_map(
            fn(EnergyTariffHoliday $holiday) => $holiday->getDate()->format('Y-m-d'),
            $logsEm->createQueryBuilder()
                ->select('h')
                ->from(EnergyTariffHoliday::class, 'h')
                ->where('h.timezone = :timezone')
                ->setParameter('timezone', $timezone)
                ->orderBy('h.date', 'ASC')
                ->getQuery()
                ->getResult()
        );
    }

    private function createTariffFromFixture(string $code, string $name): EnergyTariff {
        $logsEm = self::getContainer()->get(MeasurementLogsEntityManagerProvider::class)->get();
        $definitions = json_decode(file_get_contents(self::DEFINITIONS_FILE), true) ?: [];
        $definition = current(array_values(array_filter($definitions, fn(array $definition) => $definition['code'] === $code)));

        $tariff = new EnergyTariff();
        $tariff->setCode($code);
        $tariff->setName($name);
        $tariff->setConfig($definition['config'] ?? []);
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
