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

use App\Entity\EntityUtils;
use App\Entity\MeasurementLogs\ElectricityMeterDeltaLogItem;
use App\Entity\MeasurementLogs\EnergyTariff;
use App\Entity\MeasurementLogs\EnergyTariffHoliday;
use App\Entity\MeasurementLogs\EnergyTariffProfile;
use App\Entity\MeasurementLogs\EnergyTariffProfileAssignment;
use App\Entity\MeasurementLogs\EnergyTariffProfileTariffPeriod;
use App\Entity\MeasurementLogs\EnergyTariffResolvedZone;
use App\Enums\ChannelFunction;
use App\Enums\ChannelType;
use App\Model\MeasurementLogsEntityManagerProvider;
use App\Tests\Integration\IntegrationTestCase;
use App\Tests\Integration\Traits\TestTimeProvider;
use App\Tests\Integration\Traits\UserFixtures;
use PHPUnit\Framework\Attributes\Small;

#[Small]
class EnergyTariffResolutionIntegrationTest extends IntegrationTestCase {
    use UserFixtures;

    private const DEFINITIONS_FILE = __DIR__ . '/../../../src/DataFixtures/tariff-definitions.json';

    public function testGeneratingWarsawTariffHolidays(): void {
        $this->createTariffFromFixture('PL_G13_TAURON', 'Polish G13 Tauron');

        TestTimeProvider::setTime('2026-01-01 00:00:00 UTC');
        $this->executeCommand('supla:cyclic:generate-energy-tariff-holidays --years-ahead=1');

        $holidays = $this->fetchHolidays('Europe/Warsaw');

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
        $this->executeCommand('supla:cyclic:resolve-energy-tariffs --months-ahead=1 --from="2026-01-01 00:00:00"');

        $zones = $this->fetchResolvedZones($tariff);

        $this->assertCount(1, $zones);
        $this->assertResolvedZone($zones[0], 'ALL_DAY', '2026-01-01 00:00:00', '2026-02-01 00:00:00');
    }

    public function testMaterializingG12TariffZones(): void {
        $tariff = $this->createTariffFromFixture('PL_G12', 'Polish G12');

        TestTimeProvider::setTime('2026-01-01 00:00:00 UTC');
        $this->executeCommand('supla:cyclic:generate-energy-tariff-holidays --years-ahead=1');
        $this->executeCommand('supla:cyclic:resolve-energy-tariffs --months-ahead=1 --from="2026-01-01 00:00:00"');

        $zones = $this->fetchResolvedZones($tariff);

        $this->assertCount(63, $zones);
        $this->assertResolvedZone($zones[0], 'NIGHT', '2026-01-01 00:00:00', '2026-01-01 05:00:00');
        $this->assertResolvedZone($zones[1], 'DAY', '2026-01-01 05:00:00', '2026-01-01 21:00:00');
        $this->assertResolvedZone($zones[2], 'NIGHT', '2026-01-01 21:00:00', '2026-01-02 05:00:00');
        $this->assertResolvedZone($zones[62], 'NIGHT', '2026-01-31 21:00:00', '2026-02-01 00:00:00');
    }

    public function testMaterializingG13WinterTariffZones(): void {
        $tariff = $this->createTariffFromFixture('PL_G13_TAURON', 'Polish G13 Tauron');

        TestTimeProvider::setTime('2026-01-01 00:00:00 UTC');
        $this->executeCommand('supla:cyclic:generate-energy-tariff-holidays --years-ahead=1');
        $this->executeCommand('supla:cyclic:resolve-energy-tariffs --months-ahead=1 --from="2026-01-01 00:00:00"');

        $zones = $this->fetchResolvedZones($tariff);

        $this->assertResolvedZone($zones[0], 'OFF_PEAK', '2026-01-01 00:00:00', '2026-01-02 06:00:00');
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
        $this->executeCommand('supla:cyclic:resolve-energy-tariffs --months-ahead=1 --from="2026-07-01 00:00:00"');

        $zones = $this->fetchResolvedZones($tariff);

        $this->assertResolvedZone($zones[0], 'OFF_PEAK', '2026-07-01 00:00:00', '2026-07-01 05:00:00');
        $this->assertResolvedZone($zones[1], 'MORNING_PEAK', '2026-07-01 05:00:00', '2026-07-01 11:00:00');
        $this->assertResolvedZone($zones[2], 'OFF_PEAK', '2026-07-01 11:00:00', '2026-07-01 17:00:00');
        $this->assertResolvedZone($zones[3], 'AFTERNOON_PEAK', '2026-07-01 17:00:00', '2026-07-01 20:00:00');
        $this->assertResolvedZone($zones[4], 'OFF_PEAK', '2026-07-01 20:00:00', '2026-07-02 05:00:00');
    }

    public function testMaterializingZonesForOpenStartTariffPeriodBackfillsHistoricalLogs(): void {
        $tariff = $this->createTariffFromFixture('PL_G11', 'Polish G11');
        $user = $this->createConfirmedUser();
        $location = $this->createLocation($user);
        $device = $this->createDevice($location, [[ChannelType::ELECTRICITYMETER, ChannelFunction::ELECTRICITYMETER]]);
        $channel = $device->getChannels()[0];
        $logsEm = self::getContainer()->get(MeasurementLogsEntityManagerProvider::class)->get();

        $log = new ElectricityMeterDeltaLogItem($channel->getId(), '2025-01-01 00:15:00');
        EntityUtils::setField($log, 'phase1_fae', 100);
        EntityUtils::setField($log, 'phase2_fae', 0);
        EntityUtils::setField($log, 'phase3_fae', 0);
        EntityUtils::setField($log, 'phase1_rae', 0);
        EntityUtils::setField($log, 'phase2_rae', 0);
        EntityUtils::setField($log, 'phase3_rae', 0);
        $logsEm->persist($log);

        $profile = new EnergyTariffProfile();
        $profile->setUserId($user->getId());
        $profile->setName('Open start profile');
        $tariffPeriod = new EnergyTariffProfileTariffPeriod();
        $tariffPeriod->setTariff($tariff);
        $tariffPeriod->setValidFrom(null);
        $tariffPeriod->setValidTo(null);
        $profile->addTariffPeriod($tariffPeriod);
        $logsEm->persist($profile);

        $assignment = new EnergyTariffProfileAssignment($channel->getId());
        $assignment->setProfile($profile);
        $logsEm->persist($assignment);
        $logsEm->flush();

        TestTimeProvider::setTime('2026-01-01 00:00:00 UTC');
        $this->executeCommand('supla:cyclic:generate-energy-tariff-holidays --years-ahead=1');
        $this->executeCommand('supla:cyclic:resolve-energy-tariffs --months-ahead=1');

        $zones = $this->fetchResolvedZones($tariff);

        $this->assertNotEmpty($zones);
        $this->assertLessThanOrEqual('2025-01-01 00:00:00', $zones[0]->getPeriodStart()->format('Y-m-d H:i:s'));
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
