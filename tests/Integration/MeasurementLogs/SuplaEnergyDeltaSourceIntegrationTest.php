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

use App\Model\EnergyCost\SuplaEnergyDeltaSource;
use App\Model\MeasurementLogsEntityManagerProvider;
use App\Tests\Integration\IntegrationTestCase;
use DateTimeImmutable;
use Doctrine\DBAL\Connection;
use Supla\EnergyCostCalculator\Model\QuantityType;
use Supla\EnergyCostCalculator\Model\TimeRange;

class SuplaEnergyDeltaSourceIntegrationTest extends IntegrationTestCase {
    /** @var Connection */
    private $connection;

    /** @var SuplaEnergyDeltaSource */
    private $source;

    protected function initializeDatabaseForTests(): void {
        $entityManager = self::getContainer()->get(MeasurementLogsEntityManagerProvider::class)->get();
        $this->connection = $entityManager->getConnection();
        $this->source = new SuplaEnergyDeltaSource($entityManager);
    }

    public function testMapsAllQuantitiesToDecimalStrings(): void {
        $this->insertDelta(1, '2026-01-01 12:15:00', [
            'phase1_fae' => 100000,
            'phase2_fae' => 1,
            'phase3_fae' => null,
            'phase1_rae' => 200000,
            'phase2_rae' => 2,
            'phase3_rae' => 3,
            'phase1_fre' => 300000,
            'phase2_fre' => 4,
            'phase3_fre' => 5,
            'phase1_rre' => 400000,
            'phase2_rre' => 6,
            'phase3_rre' => 7,
            'fae_balanced' => 500008,
            'rae_balanced' => 0,
        ]);

        $deltas = iterator_to_array($this->source->getDeltas('1', $this->range('12:00', '12:15')), false);

        $this->assertCount(1, $deltas);
        $this->assertSame('2026-01-01T12:00:00+00:00', $deltas[0]->from->format(DATE_ATOM));
        $this->assertSame('2026-01-01T12:15:00+00:00', $deltas[0]->to->format(DATE_ATOM));
        $this->assertSame([
            QuantityType::ACTIVE_ENERGY_IMPORT->value => '1.00001',
            QuantityType::ACTIVE_ENERGY_EXPORT->value => '2.00005',
            QuantityType::REACTIVE_ENERGY_IMPORT->value => '3.00009',
            QuantityType::REACTIVE_ENERGY_EXPORT->value => '4.00013',
            QuantityType::ACTIVE_ENERGY_BALANCED_IMPORT->value => '5.00008',
            QuantityType::ACTIVE_ENERGY_BALANCED_EXPORT->value => '0',
        ], $deltas[0]->quantities);
    }

    public function testReturnsOnlyOverlappingCanonicalIntervalsInOrder(): void {
        $this->insertDelta(1, '2026-01-01 12:45:00');
        $this->insertDelta(1, '2026-01-01 12:15:00');
        $this->insertDelta(1, '2026-01-01 12:30:00');
        $this->insertDelta(2, '2026-01-01 12:30:00');

        $deltas = iterator_to_array($this->source->getDeltas('1', $this->range('12:15', '12:30')), false);

        $this->assertCount(1, $deltas);
        $this->assertSame('2026-01-01T12:15:00+00:00', $deltas[0]->from->format(DATE_ATOM));
        $this->assertSame('2026-01-01T12:30:00+00:00', $deltas[0]->to->format(DATE_ATOM));
    }

    public function testReturnsPartiallyOverlappingIntervalWithoutClippingAndOmitsUnavailableBalancedValues(): void {
        $this->insertDelta(1, '2026-01-01 12:30:00', ['fae_balanced' => null, 'rae_balanced' => null]);

        $deltas = iterator_to_array($this->source->getDeltas('1', $this->range('13:20', '13:25', '+01:00')), false);

        $this->assertCount(1, $deltas);
        $this->assertSame('2026-01-01T12:15:00+00:00', $deltas[0]->from->format(DATE_ATOM));
        $this->assertSame('2026-01-01T12:30:00+00:00', $deltas[0]->to->format(DATE_ATOM));
        $this->assertArrayNotHasKey(QuantityType::ACTIVE_ENERGY_BALANCED_IMPORT->value, $deltas[0]->quantities);
        $this->assertArrayNotHasKey(QuantityType::ACTIVE_ENERGY_BALANCED_EXPORT->value, $deltas[0]->quantities);
    }

    public function testPreservesFractionalSecondRangeBounds(): void {
        $this->insertDelta(1, '2026-01-01 12:30:00');

        $range = new TimeRange(
            new DateTimeImmutable('2026-01-01T12:15:00.500000+00:00'),
            new DateTimeImmutable('2026-01-01T12:15:00.600000+00:00'),
        );
        $deltas = iterator_to_array($this->source->getDeltas('1', $range), false);

        $this->assertCount(1, $deltas);
        $this->assertSame('2026-01-01T12:15:00+00:00', $deltas[0]->from->format(DATE_ATOM));
    }

    /** @param array<string, int|null> $values */
    private function insertDelta(int $channelId, string $date, array $values = []): void {
        $this->connection->insert('supla_em_delta_log', array_merge([
            'channel_id' => $channelId,
            'date' => $date,
            'phase1_fae' => 0,
            'phase1_rae' => 0,
            'phase2_fae' => 0,
            'phase2_rae' => 0,
            'phase1_fre' => 0,
            'phase1_rre' => 0,
            'phase2_fre' => 0,
            'phase2_rre' => 0,
            'phase3_fae' => 0,
            'phase3_rae' => 0,
            'phase3_fre' => 0,
            'phase3_rre' => 0,
            'fae_balanced' => null,
            'rae_balanced' => null,
        ], $values));
    }

    private function range(string $from, string $to, string $offset = '+00:00'): TimeRange {
        return new TimeRange(
            new DateTimeImmutable("2026-01-01T$from:00$offset"),
            new DateTimeImmutable("2026-01-01T$to:00$offset"),
        );
    }
}
