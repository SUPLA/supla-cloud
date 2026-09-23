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

use App\Utils\ElectricityMeterValueConverter;
use DateTimeImmutable;
use DateTimeZone;
use Doctrine\DBAL\ParameterType;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Supla\EnergyCostCalculator\Contract\EnergyDeltaSource;
use Supla\EnergyCostCalculator\Model\EnergyDelta;
use Supla\EnergyCostCalculator\Model\QuantityType;
use Supla\EnergyCostCalculator\Model\TimeRange;

final class SuplaEnergyDeltaSource implements EnergyDeltaSource {
    private const SLOT_DURATION = '15 minutes';
    private const PHASE_COLUMNS = [
        QuantityType::ACTIVE_ENERGY_IMPORT->value => ['phase1_fae', 'phase2_fae', 'phase3_fae'],
        QuantityType::ACTIVE_ENERGY_EXPORT->value => ['phase1_rae', 'phase2_rae', 'phase3_rae'],
        QuantityType::REACTIVE_ENERGY_IMPORT->value => ['phase1_fre', 'phase2_fre', 'phase3_fre'],
        QuantityType::REACTIVE_ENERGY_EXPORT->value => ['phase1_rre', 'phase2_rre', 'phase3_rre'],
    ];

    public function __construct(private readonly EntityManagerInterface $measurementLogsEntityManager) {
    }

    public function getDeltas(string $meterId, TimeRange $range): iterable {
        if (!ctype_digit($meterId)) {
            throw new InvalidArgumentException("Invalid electricity meter ID: $meterId");
        }

        $utc = new DateTimeZone('UTC');
        $rows = $this->measurementLogsEntityManager->getConnection()->executeQuery(
            'SELECT date,
                    phase1_fae, phase2_fae, phase3_fae,
                    phase1_rae, phase2_rae, phase3_rae,
                    phase1_fre, phase2_fre, phase3_fre,
                    phase1_rre, phase2_rre, phase3_rre,
                    fae_balanced, rae_balanced
               FROM supla_em_delta_log
              WHERE channel_id = :channelId
                AND date > :rangeFrom
                AND date < :rangeToPlusSlot
              ORDER BY date ASC',
            [
                'channelId' => (int)$meterId,
                'rangeFrom' => $range->from->setTimezone($utc)->format('Y-m-d H:i:s.u'),
                'rangeToPlusSlot' => $range->to->setTimezone($utc)->modify('+' . self::SLOT_DURATION)->format('Y-m-d H:i:s.u'),
            ],
            ['channelId' => ParameterType::INTEGER]
        );

        foreach ($rows->iterateAssociative() as $row) {
            $to = (new DateTimeImmutable((string)$row['date'], $utc))->setTimezone($utc);
            $quantities = [];
            foreach (self::PHASE_COLUMNS as $quantity => $columns) {
                $rawValue = array_sum(array_map(fn(string $column) => (int)($row[$column] ?? 0), $columns));
                $quantities[$quantity] = ElectricityMeterValueConverter::rawEnergyToDecimal($rawValue);
            }
            if ($row['fae_balanced'] !== null) {
                $quantities[QuantityType::ACTIVE_ENERGY_BALANCED_IMPORT->value] =
                    ElectricityMeterValueConverter::rawEnergyToDecimal((int)$row['fae_balanced']);
            }
            if ($row['rae_balanced'] !== null) {
                $quantities[QuantityType::ACTIVE_ENERGY_BALANCED_EXPORT->value] =
                    ElectricityMeterValueConverter::rawEnergyToDecimal((int)$row['rae_balanced']);
            }

            yield new EnergyDelta($to->modify('-' . self::SLOT_DURATION), $to, $quantities);
        }
    }
}
