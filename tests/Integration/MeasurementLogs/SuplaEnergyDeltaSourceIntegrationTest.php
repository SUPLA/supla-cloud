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

use App\Entity\Main\IODeviceChannel;
use App\Entity\Main\User;
use App\Enums\ChannelFunction;
use App\Exception\ApiException;
use App\Model\EnergyCost\EnergyCostPlanCalculator;
use App\Model\EnergyCost\SuplaEnergyDeltaSource;
use App\Model\MeasurementLogsEntityManagerProvider;
use App\Repository\EnergyCostPlanAssignmentRepository;
use App\Tests\Integration\IntegrationTestCase;
use DateTimeImmutable;
use Doctrine\DBAL\Connection;
use Supla\EnergyCostCalculator\Contract\ReferenceDataSource;
use Supla\EnergyCostCalculator\Engine\CostCalculator;
use Supla\EnergyCostCalculator\Model\QuantityType;
use Supla\EnergyCostCalculator\Model\TimeRange;
use Supla\EnergyCostCalculator\Plan\CostPlanCompiler;
use Supla\EnergyCostCalculator\Plan\CostPlanDefinitionParser;

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

    public function testReturnsPartiallyOverlappingIntervalWithoutClipping(): void {
        $this->insertDelta(1, '2026-01-01 12:30:00', ['fae_balanced' => null, 'rae_balanced' => null]);

        $deltas = iterator_to_array($this->source->getDeltas('1', $this->range('13:20', '13:25', '+01:00')), false);

        $this->assertCount(1, $deltas);
        $this->assertSame('2026-01-01T12:15:00+00:00', $deltas[0]->from->format(DATE_ATOM));
        $this->assertSame('2026-01-01T12:30:00+00:00', $deltas[0]->to->format(DATE_ATOM));
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

    public function testObservationBoundariesUseEntireHistoryAndCanonicalDeltaTimes(): void {
        $this->assertNull($this->source->getObservationRange('1'));
        $this->insertDelta(1, '2026-09-02 22:15:00'); // September 3, 00:00 local interval start.
        $this->insertDelta(1, '2026-10-08 22:00:00'); // October 9, 00:00 local exclusive end.
        $this->insertDelta(2, '2026-01-01 00:00:00');
        $this->insertDelta(2, '2027-01-01 00:00:00');
        $history = $this->source->getObservationRange('1');
        $this->assertSame('UTC', $history->from->getTimezone()->getName());
        $this->assertSame('2026-09-02T22:00:00+00:00', $history->from->format(DATE_ATOM));
        $this->assertSame('2026-10-08T22:00:00+00:00', $history->to->format(DATE_ATOM));
        $deltas = iterator_to_array($this->source->getDeltas('1', $history), false);
        $this->assertEquals($history->from, $deltas[0]->from);
        $this->assertEquals($history->to, $deltas[1]->to);
    }

    public function testFullYearChargesOnlySeptemberAndOctoberIncludingZeroConsumption(): void {
        $this->insertDelta(1, '2026-09-02 22:15:00');
        $this->insertDelta(1, '2026-10-08 22:00:00');
        $result = $this->calculate('2026-01-01T00:00:00+01:00', '2027-01-01T00:00:00+01:00');
        $this->assertSame('2025-12-31T23:00:00+00:00', $result->range->from->format(DATE_ATOM));
        $this->assertSame('2026-12-31T23:00:00+00:00', $result->range->to->format(DATE_ATOM));
        $this->assertSame(2, $result->processedDeltaCount);
        $this->assertSame('0', $result->usage['ACTIVE_ENERGY_IMPORT']);
        $this->assertCount(12, $result->billingPeriods);
        foreach ($result->billingPeriods as $index => $period) {
            $observed = in_array($index, [8, 9], true);
            $this->assertSame($observed ? '12.3' : '0', $period['costs']['gross']['periodic']['total']);
            $this->assertSame($observed ? '2.3' : '0', $period['costs']['taxes']['total']);
            $this->assertSame($observed ? '12.3' : '0', $period['costs']['gross']['total']);
        }
        $this->assertSame('2', $result->periodicCharges[0]['calculated']['units']);
        $this->assertSame('24.6', $result->periodicCharges[0]['calculated']['amounts']['gross']);
        $this->assertSame('24.6', $result->costs['gross']['total']);
        $this->assertSame('24.6', $result->reportSummary['costs']['gross']['total']);
        $this->assertSame('4.6', $result->reportSummary['costs']['taxes']['total']);
        $this->assertTrue($result->reportSummary['fixedChargesComplete']);
    }

    public function testNoSelectedMeasurementsStillUsesHistoryForPeriodCompleteness(): void {
        $this->insertDelta(1, '2026-09-02 22:15:00');
        $this->insertDelta(1, '2026-10-08 22:00:00');
        $inside = $this->calculate('2026-09-10T00:00:00+02:00', '2026-09-20T00:00:00+02:00');
        $this->assertSame(0, $inside->processedDeltaCount);
        $this->assertFalse($inside->reportSummary['fixedChargesComplete']);
        $this->assertNull($inside->billingPeriods[0]['costs']['gross']['periodic']['total']);
        $outside = $this->calculate('2026-01-10T00:00:00+01:00', '2026-01-20T00:00:00+01:00');
        $this->assertSame(0, $outside->processedDeltaCount);
        $this->assertTrue($outside->reportSummary['fixedChargesComplete']);
        $this->assertSame('0', $outside->reportSummary['costs']['gross']['total']);
        $this->assertSame('0', $outside->billingPeriods[0]['costs']['gross']['periodic']['total']);
    }

    public function testCompletePeriodWithoutSelectedMeasurementsInsideHistoryStillHasFixedCharges(): void {
        $this->insertDelta(1, '2026-09-02 22:15:00');
        $this->insertDelta(1, '2026-11-08 23:00:00');
        $result = $this->calculate('2026-10-01T00:00:00+02:00', '2026-11-01T00:00:00+01:00');
        $this->assertSame(0, $result->processedDeltaCount);
        $this->assertSame('12.3', $result->reportSummary['costs']['gross']['total']);
        $this->assertTrue($result->reportSummary['fixedChargesComplete']);
    }

    public function testHistoryEndingExactlyAtBillingBoundaryDoesNotChargeFollowingPeriod(): void {
        $this->insertDelta(1, '2026-09-01 22:15:00');
        $this->insertDelta(1, '2026-09-30 22:00:00');
        $result = $this->calculate('2026-09-01T00:00:00+02:00', '2026-11-01T00:00:00+01:00');
        $this->assertSame('12.3', $result->reportSummary['costs']['gross']['periodic']['total']);
        $this->assertSame('0', $result->billingPeriods[1]['costs']['gross']['periodic']['total']);
    }

    public function testEmptyHistoryDoesNotPassUnrestrictedNullObservationRange(): void {
        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('No logs are available for the selected range.');
        $this->calculate('2026-01-01T00:00:00+01:00', '2027-01-01T00:00:00+01:00');
    }

    private function calculate(string $from, string $to): \Supla\EnergyCostCalculator\Engine\CalculationResult {
        $user = $this->createMock(User::class);
        $channel = $this->createMock(IODeviceChannel::class);
        $channel->method('belongsToUser')->with($user)->willReturn(true);
        $channel->method('getFunction')->willReturn(ChannelFunction::ELECTRICITYMETER());
        $channel->method('getId')->willReturn(1);
        $calculator = new EnergyCostPlanCalculator(
            $this->createMock(EnergyCostPlanAssignmentRepository::class),
            new CostPlanDefinitionParser(),
            new CostPlanCompiler(),
            new CostCalculator($this->source, $this->createMock(ReferenceDataSource::class)),
            $this->source,
        );
        return $calculator->calculateConfiguration($user, $channel, [
            'version' => 2, 'currency' => 'PLN', 'timezone' => 'Europe/Warsaw',
            'taxContext' => ['jurisdiction' => 'PL', 'customerClass' => 'HOUSEHOLD'],
            'billingCycles' => [['validFrom' => null, 'validTo' => null, 'anchor' => '2026-01-01', 'length' => 1, 'unit' => 'MONTH']],
            'periods' => [['validFrom' => null, 'validTo' => null, 'components' => [
                ['kind' => 'SUPPLIER_FIXED', 'componentId' => 'fixed', 'rate' => '10', 'per' => 'MONTH', 'taxTreatment' => ['included' => []]],
            ]]],
        ], new DateTimeImmutable($from), new DateTimeImmutable($to));
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
