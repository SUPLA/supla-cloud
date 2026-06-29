<?php

namespace App\Model\MeasurementLogs;

use App\Enums\EnergyPriceComponent;

class EnergyCostLogHydrator {
    public function hydrateLogs(array $rows): array {
        $logs = [];
        foreach ($rows as $row) {
            $key = (string)$row['date_timestamp'];
            if (!isset($logs[$key])) {
                $phase1 = (int)$row['phase1_fae'];
                $phase2 = (int)$row['phase2_fae'];
                $phase3 = (int)$row['phase3_fae'];
                $logs[$key] = [
                    'dateTimestamp' => (int)$row['date_timestamp'],
                    'slotStartTimestamp' => (int)$row['slot_start_timestamp'],
                    'profileId' => $row['profile_id'] !== null ? (int)$row['profile_id'] : null,
                    'tariffId' => $row['tariff_id'] !== null ? (int)$row['tariff_id'] : null,
                    'zoneCode' => $row['zone_code'],
                    'pricePeriodId' => $row['price_period_id'] !== null ? (int)$row['price_period_id'] : null,
                    'pricePeriodValidFrom' => $row['price_period_valid_from'] ? new \DateTime($row['price_period_valid_from'], new \DateTimeZone('UTC')) : null,
                    'billingPeriodLength' => $row['billing_period_length'] !== null ? (int)$row['billing_period_length'] : null,
                    'billingPeriodUnit' => $row['billing_period_unit'],
                    'usage' => [
                        'phase1Fae' => $phase1,
                        'phase2Fae' => $phase2,
                        'phase3Fae' => $phase3,
                        'totalFae' => $phase1 + $phase2 + $phase3,
                        'totalKwh' => round((float)$row['total_kwh'], 6),
                    ],
                    'costs' => null,
                ];
            }

            if (!$row['component_code']) {
                continue;
            }

            if ($logs[$key]['costs'] === null) {
                $logs[$key]['costs'] = [
                    'currency' => $row['currency'],
                    'total' => 0.0,
                    'byComponent' => [],
                    'byZone' => [],
                    'byPhase' => ['phase1' => 0.0, 'phase2' => 0.0, 'phase3' => 0.0],
                ];
            }

            $componentCode = EnergyPriceComponent::from((int)$row['component_code'])->name;
            $componentCost = round((float)$row['total_kwh'] * (float)$row['amount'], 6);
            $phase1Cost = round((float)$row['phase1_kwh'] * (float)$row['amount'], 6);
            $phase2Cost = round((float)$row['phase2_kwh'] * (float)$row['amount'], 6);
            $phase3Cost = round((float)$row['phase3_kwh'] * (float)$row['amount'], 6);

            $logs[$key]['costs']['total'] += $componentCost;
            $logs[$key]['costs']['byComponent'][$componentCode] = ($logs[$key]['costs']['byComponent'][$componentCode] ?? 0) + $componentCost;
            if ($row['zone_code']) {
                $logs[$key]['costs']['byZone'][$row['zone_code']] = ($logs[$key]['costs']['byZone'][$row['zone_code']] ?? 0) + $componentCost;
            }
            $logs[$key]['costs']['byPhase']['phase1'] += $phase1Cost;
            $logs[$key]['costs']['byPhase']['phase2'] += $phase2Cost;
            $logs[$key]['costs']['byPhase']['phase3'] += $phase3Cost;
        }

        foreach ($logs as &$log) {
            if ($log['costs']) {
                $log['costs']['total'] = round($log['costs']['total'], 6);
                foreach ($log['costs']['byComponent'] as $component => $amount) {
                    $log['costs']['byComponent'][$component] = round($amount, 6);
                }
                foreach ($log['costs']['byZone'] as $zone => $amount) {
                    $log['costs']['byZone'][$zone] = round($amount, 6);
                }
                foreach ($log['costs']['byPhase'] as $phase => $amount) {
                    $log['costs']['byPhase'][$phase] = round($amount, 6);
                }
            }
        }

        return array_values($logs);
    }
}
