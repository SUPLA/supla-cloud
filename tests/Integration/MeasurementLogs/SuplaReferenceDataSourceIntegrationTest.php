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

use App\Model\EnergyCost\SuplaReferenceDataSource;
use App\Model\MeasurementLogsEntityManagerProvider;
use App\Tests\Integration\IntegrationTestCase;
use DateTimeImmutable;
use Doctrine\DBAL\Connection;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use Supla\EnergyCostCalculator\Exception\MissingReferenceDataException;
use Supla\EnergyCostCalculator\Model\ReferenceDataId;
use Supla\EnergyCostCalculator\Model\TimeRange;
use Supla\EnergyCostCalculator\Reference\ReferenceSeries;

class SuplaReferenceDataSourceIntegrationTest extends IntegrationTestCase {
    /** @var Connection */
    private $connection;

    /** @var SuplaReferenceDataSource */
    private $source;

    protected function initializeDatabaseForTests(): void {
        $entityManager = self::getContainer()->get(MeasurementLogsEntityManagerProvider::class)->get();
        $this->connection = $entityManager->getConnection();
        $this->source = new SuplaReferenceDataSource($entityManager);
    }

    #[DataProvider('referenceSources')]
    public function testMapsSupportedReferenceSources(string $id, string $column, string $expected, ?string $unit): void {
        $this->insertReference('2026-01-01 12:00:00', '2026-01-01 12:14:59', [$column => $expected]);

        $intervals = iterator_to_array($this->source->get(new ReferenceDataId($id), $this->range('12:00', '12:15')), false);

        $this->assertCount(1, $intervals);
        $this->assertSame($expected, $intervals[0]->value);
        $this->assertSame($unit, $intervals[0]->unit);
        $this->assertSame('2026-01-01T12:00:00+00:00', $intervals[0]->from->format(DATE_ATOM));
        $this->assertSame('2026-01-01T12:15:00+00:00', $intervals[0]->to->format(DATE_ATOM));
    }

    /** @return list<array{string, string, string, string|null}> */
    public static function referenceSources(): array {
        return [
            ['PL.PSE.RCE', 'rce', '-12.3400', 'PLN/MWh'],
            ['PL.PSE.PDGSZ', 'pdgsz', '0', null],
            ['PL.TGE.FIXING1', 'fixing1', '436.2000', 'PLN/MWh'],
            ['PL.TGE.FIXING1_HOURLY', 'fixing1_hourly', '437.2000', 'PLN/MWh'],
            ['PL.TGE.FIXING2', 'fixing2', '448.9100', 'PLN/MWh'],
            ['PL.TGE.FIXING2_HOURLY', 'fixing2_hourly', '449.9100', 'PLN/MWh'],
        ];
    }

    public function testUsesHalfOpenOverlapAndReturnsIntervalsInOrderWithoutClipping(): void {
        $this->insertReference('2026-01-01 12:15:00', '2026-01-01 12:29:59', ['rce' => '2.0000']);
        $this->insertReference('2026-01-01 12:00:00', '2026-01-01 12:14:59', ['rce' => '1.0000']);

        $intervals = iterator_to_array(
            $this->source->get(new ReferenceDataId('PL.PSE.RCE'), $this->range('13:10', '13:20', '+01:00')),
            false
        );

        $this->assertCount(2, $intervals);
        $this->assertSame('2026-01-01T12:00:00+00:00', $intervals[0]->from->format(DATE_ATOM));
        $this->assertSame('2026-01-01T12:15:00+00:00', $intervals[0]->to->format(DATE_ATOM));
        $this->assertSame('2026-01-01T12:15:00+00:00', $intervals[1]->from->format(DATE_ATOM));
        $this->assertSame('2026-01-01T12:30:00+00:00', $intervals[1]->to->format(DATE_ATOM));
    }

    public function testOmitsNullValuesAndLetsReferenceSeriesReportMissingData(): void {
        $this->insertReference('2026-01-01 12:00:00', '2026-01-01 12:14:59', ['fixing1' => '10.0000']);

        $id = new ReferenceDataId('PL.PSE.RCE');
        $series = new ReferenceSeries($id, $this->source->get($id, $this->range('12:00', '12:15')));

        $this->expectException(MissingReferenceDataException::class);
        $series->valueAt(new DateTimeImmutable('2026-01-01T12:00:00+00:00'));
    }

    public function testPreservesFractionalSecondRangeBounds(): void {
        $this->insertReference('2026-01-01 12:00:00', '2026-01-01 12:14:59', ['rce' => '1.0000']);
        $this->insertReference('2026-01-01 12:15:00', '2026-01-01 12:29:59', ['rce' => '2.0000']);
        $range = new TimeRange(
            new DateTimeImmutable('2026-01-01T12:14:59.500000+00:00'),
            new DateTimeImmutable('2026-01-01T12:15:00.500000+00:00'),
        );

        $intervals = iterator_to_array($this->source->get(new ReferenceDataId('PL.PSE.RCE'), $range), false);

        $this->assertCount(2, $intervals);
        $this->assertSame('1.0000', $intervals[0]->value);
        $this->assertSame('2.0000', $intervals[1]->value);
    }

    public function testRejectsUnsupportedReferenceSource(): void {
        $this->expectException(InvalidArgumentException::class);
        $this->source->get(new ReferenceDataId('UNKNOWN'), $this->range('12:00', '12:15'));
    }

    /** @param array<string, int|string|null> $values */
    private function insertReference(string $from, string $to, array $values): void {
        $this->connection->insert('supla_energy_price_log', array_merge([
            'date_from' => $from,
            'date_to' => $to,
            'rce' => null,
            'pdgsz' => null,
            'fixing1' => null,
            'fixing2' => null,
            'fixing1_hourly' => null,
            'fixing2_hourly' => null,
        ], $values));
    }

    private function range(string $from, string $to, string $offset = '+00:00'): TimeRange {
        return new TimeRange(
            new DateTimeImmutable("2026-01-01T$from:00$offset"),
            new DateTimeImmutable("2026-01-01T$to:00$offset"),
        );
    }
}
