<?php

namespace App\Model\MeasurementLogs;

use App\Enums\BillingPeriodUnit;
use Doctrine\ORM\EntityManagerInterface;

class EnergyCostSummaryBuilder {
    public function __construct(
        private readonly EntityManagerInterface $measurementLogsEntityManager,
        private readonly EnergyCostLogHydrator $costLogHydrator,
        private readonly EnergyBillingPeriodResolver $billingPeriodResolver,
        private readonly EnergyFixedCostCalculator $fixedCostCalculator,
    ) {
    }

    public function buildSummaries(
        int $channelId,
        int $afterTimestamp,
        int $beforeTimestamp,
        EnergyCostRowFetcher $energyCostRowFetcher
    ): array {
        $summaries = [];
        $firstFetchedLogTimestamp = null;
        $tariffTimezones = [];

        $energyCostRowFetcher->forEachCostRowBatch(
            $channelId,
            $afterTimestamp,
            $beforeTimestamp,
            function (array $rows) use (&$summaries, &$firstFetchedLogTimestamp, &$tariffTimezones): void {
                $logs = $this->costLogHydrator->hydrateLogs($rows);
                if (!$logs) {
                    return;
                }

                $firstFetchedLogTimestamp ??= $logs[0]['dateTimestamp'];
                $this->loadTariffTimezones($this->collectMissingTariffIds($logs, $tariffTimezones), $tariffTimezones);
                $this->accumulateSummaries($summaries, $logs, $tariffTimezones);
            }
        );

        if ($firstFetchedLogTimestamp === null && $afterTimestamp <= 0) {
            return [];
        }

        $effectiveAfterTimestamp = $afterTimestamp > 0 ? $afterTimestamp : ($firstFetchedLogTimestamp ?? 0);
        if ($effectiveAfterTimestamp > 0) {
            $this->fixedCostCalculator->applyFixedCostsToSummaries($summaries, $channelId, $effectiveAfterTimestamp, $beforeTimestamp);
        }

        foreach ($summaries as &$summary) {
            $summary['usage']['totalFaeKwh'] = round($summary['usage']['totalFaeKwh'], 6);
            foreach ($summary['usage']['byPhase'] as $phase => $amount) {
                $summary['usage']['byPhase'][$phase] = round($amount, 6);
            }
            $summary['costs']['total'] = round($summary['costs']['total'], 6);
            foreach ($summary['costs']['byComponent'] as $component => $amount) {
                $summary['costs']['byComponent'][$component] = round($amount, 6);
            }
            foreach ($summary['costs']['byZone'] as $zone => $amount) {
                $summary['costs']['byZone'][$zone] = round($amount, 6);
            }
            foreach ($summary['costs']['byPhase'] as $phase => $amount) {
                $summary['costs']['byPhase'][$phase] = round($amount, 6);
            }
        }

        ksort($summaries);
        return array_values($summaries);
    }

    private function accumulateSummaries(array &$summaries, array $logs, array $tariffTimezones): void {
        foreach ($logs as $log) {
            $context = $this->resolveBillingContext($log, $tariffTimezones);
            $key = $context['key'];
            if (!isset($summaries[$key])) {
                $summaries[$key] = [
                    'periodStart' => $context['periodStart'],
                    'periodEnd' => $context['periodEnd'],
                    'timezone' => $context['timezone'],
                    'usage' => [
                        'totalFaeKwh' => 0.0,
                        'byPhase' => ['phase1' => 0.0, 'phase2' => 0.0, 'phase3' => 0.0],
                    ],
                    'costs' => [
                        'currency' => $log['costs']['currency'] ?? 'PLN',
                        'total' => 0.0,
                        'byComponent' => [],
                        'byZone' => [],
                        'byPhase' => ['phase1' => 0.0, 'phase2' => 0.0, 'phase3' => 0.0],
                    ],
                ];
            }

            $summaries[$key]['usage']['totalFaeKwh'] += $log['usage']['totalFaeKwh'];
            $summaries[$key]['usage']['byPhase']['phase1'] += $log['usage']['phase1FaeKwh'];
            $summaries[$key]['usage']['byPhase']['phase2'] += $log['usage']['phase2FaeKwh'];
            $summaries[$key]['usage']['byPhase']['phase3'] += $log['usage']['phase3FaeKwh'];

            if (!$log['costs']) {
                continue;
            }

            $summaries[$key]['costs']['total'] += $log['costs']['total'];
            foreach ($log['costs']['byComponent'] as $component => $amount) {
                $summaries[$key]['costs']['byComponent'][$component] = ($summaries[$key]['costs']['byComponent'][$component] ?? 0) + $amount;
            }
            foreach ($log['costs']['byZone'] as $zone => $amount) {
                $summaries[$key]['costs']['byZone'][$zone] = ($summaries[$key]['costs']['byZone'][$zone] ?? 0) + $amount;
            }
            foreach ($log['costs']['byPhase'] as $phase => $amount) {
                $summaries[$key]['costs']['byPhase'][$phase] += $amount;
            }
        }
    }

    private function collectMissingTariffIds(array $logs, array $tariffTimezones): array {
        $missingTariffIds = [];
        foreach ($logs as $log) {
            if ($log['tariffId'] && !isset($tariffTimezones[$log['tariffId']])) {
                $missingTariffIds[$log['tariffId']] = $log['tariffId'];
            }
        }
        return array_values($missingTariffIds);
    }

    private function loadTariffTimezones(array $tariffIds, array &$tariffTimezones): void {
        if (!$tariffIds) {
            return;
        }

        $rows = $this->measurementLogsEntityManager->getConnection()->executeQuery(
            'SELECT id, config_json FROM supla_energy_tariff WHERE id IN (:ids)',
            ['ids' => $tariffIds],
            ['ids' => \Doctrine\DBAL\ArrayParameterType::INTEGER]
        )->fetchAllAssociative();

        foreach ($rows as $row) {
            $config = json_decode($row['config_json'] ?? '[]', true) ?: [];
            $tariffTimezones[(int)$row['id']] = $config['timezone'] ?? 'UTC';
        }
    }

    private function resolveBillingContext(array $log, array $tariffTimezones): array {
        $timezone = new \DateTimeZone(($log['tariffId'] && isset($tariffTimezones[$log['tariffId']])) ? $tariffTimezones[$log['tariffId']] : 'UTC');
        if (!$log['billingPeriodUnit'] || !$log['billingPeriodLength']) {
            return $this->billingPeriodResolver->resolveDefaultBillingPeriodForTimestamp($log['slotStartTimestamp'], $timezone);
        }

        return $this->billingPeriodResolver->resolveBillingPeriodForTimestamp(
            $log['slotStartTimestamp'],
            $timezone,
            $log['pricePeriodValidFrom'],
            $log['billingPeriodLength'],
            BillingPeriodUnit::from($log['billingPeriodUnit'])
        );
    }
}
