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

namespace App\Model\EnergyCost;

use DateTimeImmutable;
use DateTimeZone;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Supla\EnergyCostCalculator\Contract\ReferenceDataSource;
use Supla\EnergyCostCalculator\Model\ReferenceDataId;
use Supla\EnergyCostCalculator\Model\ReferenceInterval;
use Supla\EnergyCostCalculator\Model\TimeRange;

final class SuplaReferenceDataSource implements ReferenceDataSource {
    private const SOURCES = [
        'PL.PSE.RCE' => ['column' => 'rce', 'unit' => 'PLN/MWh'],
        'PL.PSE.PDGSZ' => ['column' => 'pdgsz', 'unit' => null],
        'PL.TGE.FIXING1' => ['column' => 'fixing1', 'unit' => 'PLN/MWh'],
        'PL.TGE.FIXING1_HOURLY' => ['column' => 'fixing1_hourly', 'unit' => 'PLN/MWh'],
        'PL.TGE.FIXING2' => ['column' => 'fixing2', 'unit' => 'PLN/MWh'],
        'PL.TGE.FIXING2_HOURLY' => ['column' => 'fixing2_hourly', 'unit' => 'PLN/MWh'],
    ];

    public function __construct(private readonly EntityManagerInterface $measurementLogsEntityManager) {
    }

    public function get(ReferenceDataId $id, TimeRange $range): iterable {
        $source = self::SOURCES[$id->value] ?? throw new InvalidArgumentException("Unsupported reference data ID: {$id->value}");
        return $this->getIntervals($source['column'], $source['unit'], $range);
    }

    /** @return iterable<ReferenceInterval> */
    private function getIntervals(string $column, ?string $unit, TimeRange $range): iterable {
        $utc = new DateTimeZone('UTC');
        $rows = $this->measurementLogsEntityManager->getConnection()->executeQuery(
            "SELECT date_from, date_to, $column AS reference_value
               FROM supla_energy_price_log
              WHERE date_to > :rangeFromMinusOneSecond
                AND date_from < :rangeTo
                AND $column IS NOT NULL
              ORDER BY date_from ASC",
            [
                'rangeFromMinusOneSecond' => $range->from->setTimezone($utc)->modify('-1 second')->format('Y-m-d H:i:s.u'),
                'rangeTo' => $range->to->setTimezone($utc)->format('Y-m-d H:i:s.u'),
            ]
        );

        foreach ($rows->iterateAssociative() as $row) {
            $from = (new DateTimeImmutable((string)$row['date_from'], $utc))->setTimezone($utc);
            $to = (new DateTimeImmutable((string)$row['date_to'], $utc))->setTimezone($utc)->modify('+1 second');
            yield new ReferenceInterval($from, $to, (string)$row['reference_value'], $unit);
        }
    }
}
