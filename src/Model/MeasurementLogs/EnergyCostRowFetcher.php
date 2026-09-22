<?php

namespace App\Model\MeasurementLogs;

use App\Entity\MeasurementLogs\EnergyTariffProfileAssignment;
use App\Entity\MeasurementLogs\EnergyTariffProfilePriceItem;
use App\Entity\MeasurementLogs\EnergyTariffProfilePricePeriod;
use App\Entity\MeasurementLogs\EnergyTariffProfileTariffPeriod;
use App\Enums\EnergyPriceUnit;
use App\Utils\DateUtils;
use App\Utils\ElectricityMeterValueConverter;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\ORM\EntityManagerInterface;

class EnergyCostRowFetcher {
    public function __construct(
        private readonly EntityManagerInterface $measurementLogsEntityManager,
        private readonly TariffZoneResolver $tariffZoneResolver,
        private readonly int $recordLimitPerRequest = 10000,
    ) {
    }

    public function getRecordLimitPerRequest(): int {
        return $this->recordLimitPerRequest;
    }

    public function fetchCostRows(
        int $channelId,
        int $afterTimestamp,
        int $beforeTimestamp,
        bool $orderDesc,
        int $limit,
        int $offset
    ): array {
        $deltaRows = $this->fetchDeltaRows($channelId, $afterTimestamp, $beforeTimestamp, $orderDesc, $limit, $offset);
        if (!$deltaRows) {
            return [];
        }

        $context = $this->loadProfileContext($channelId, $deltaRows);
        return $this->expandCostRows($deltaRows, $context);
    }

    public function forEachCostRowBatch(int $channelId, int $afterTimestamp, int $beforeTimestamp, callable $batchConsumer): void {
        $cursorTimestamp = $afterTimestamp;

        do {
            $batch = $this->fetchCostRows(
                $channelId,
                $cursorTimestamp,
                $beforeTimestamp,
                false,
                $this->recordLimitPerRequest,
                0
            );
            if (!$batch) {
                break;
            }

            $batchConsumer($batch);

            $lastTimestamp = (int)end($batch)['date_timestamp'];
            if ($lastTimestamp <= $cursorTimestamp) {
                break;
            }
            $cursorTimestamp = $lastTimestamp;
        } while (true);
    }

    private function fetchDeltaRows(
        int $channelId,
        int $afterTimestamp,
        int $beforeTimestamp,
        bool $orderDesc,
        int $limit,
        int $offset
    ): array {
        $order = $orderDesc ? 'DESC' : 'ASC';
        $where = 'WHERE d.channel_id = :channelId ';
        if ($afterTimestamp > 0) {
            $where .= 'AND d.date > :afterDate ';
        }
        if ($beforeTimestamp > 0) {
            $where .= 'AND d.date < :beforeDate ';
        }

        $sql = "SELECT d.channel_id,
                d.date,
                d.phase1_fae,
                d.phase2_fae,
                d.phase3_fae,
                d.phase1_rae,
                d.phase2_rae,
                d.phase3_rae
            FROM supla_em_delta_log d
            $where
            ORDER BY d.date $order
            LIMIT :limit OFFSET :offset";

        $stmt = $this->measurementLogsEntityManager->getConnection()->prepare($sql);
        $stmt->bindValue('channelId', $channelId, 'integer');
        if ($afterTimestamp > 0) {
            $stmt->bindValue('afterDate', DateUtils::timestampToMysqlUtc($afterTimestamp), 'string');
        }
        if ($beforeTimestamp > 0) {
            $stmt->bindValue('beforeDate', DateUtils::timestampToMysqlUtc($beforeTimestamp), 'string');
        }
        $stmt->bindValue('limit', min(max($limit, 1), $this->recordLimitPerRequest), 'integer');
        $stmt->bindValue('offset', max($offset, 0), 'integer');

        $rows = $stmt->executeQuery()->fetchAllAssociative();
        foreach ($rows as &$row) {
            $dateTimestamp = (new \DateTime($row['date'], new \DateTimeZone('UTC')))->getTimestamp();
            $phase1 = (int)($row['phase1_fae'] ?? 0);
            $phase2 = (int)($row['phase2_fae'] ?? 0);
            $phase3 = (int)($row['phase3_fae'] ?? 0);
            $phase1Reverse = (int)($row['phase1_rae'] ?? 0);
            $phase2Reverse = (int)($row['phase2_rae'] ?? 0);
            $phase3Reverse = (int)($row['phase3_rae'] ?? 0);
            $row['date_timestamp'] = $dateTimestamp;
            $row['slot_start_timestamp'] = $dateTimestamp - (15 * 60);
            $row['total_kwh'] = ElectricityMeterValueConverter::rawEnergyToFloat($phase1 + $phase2 + $phase3);
            $row['phase1_kwh'] = ElectricityMeterValueConverter::rawEnergyToFloat($phase1);
            $row['phase2_kwh'] = ElectricityMeterValueConverter::rawEnergyToFloat($phase2);
            $row['phase3_kwh'] = ElectricityMeterValueConverter::rawEnergyToFloat($phase3);
            $row['phase1_reverse_kwh'] = ElectricityMeterValueConverter::rawEnergyToFloat($phase1Reverse);
            $row['phase2_reverse_kwh'] = ElectricityMeterValueConverter::rawEnergyToFloat($phase2Reverse);
            $row['phase3_reverse_kwh'] = ElectricityMeterValueConverter::rawEnergyToFloat($phase3Reverse);
            $row['reverse_kwh'] = ElectricityMeterValueConverter::rawEnergyToFloat($phase1Reverse + $phase2Reverse + $phase3Reverse);
        }

        return $rows;
    }

    private function loadProfileContext(int $channelId, array $deltaRows): ?array {
        $assignment = $this->measurementLogsEntityManager->createQueryBuilder()
            ->select('assignment', 'profile', 'tariffPeriod', 'tariff', 'pricePeriod', 'item')
            ->from(EnergyTariffProfileAssignment::class, 'assignment')
            ->leftJoin('assignment.profile', 'profile')
            ->leftJoin('profile.tariffPeriods', 'tariffPeriod')
            ->leftJoin('tariffPeriod.tariff', 'tariff')
            ->leftJoin('tariffPeriod.pricePeriods', 'pricePeriod')
            ->leftJoin('pricePeriod.items', 'item')
            ->where('assignment.channelId = :channelId')
            ->setParameter('channelId', $channelId)
            ->getQuery()
            ->getOneOrNullResult();

        if (!$assignment || !$assignment->getProfile()) {
            return null;
        }

        $slotTimestamps = array_column($deltaRows, 'slot_start_timestamp');
        $rangeStartTimestamp = min($slotTimestamps);
        $rangeEndTimestamp = max(array_column($deltaRows, 'date_timestamp'));

        $tariffPeriods = $this->normalizeTariffPeriods($assignment->getProfile()->getTariffPeriods()->toArray());
        $tariffIds = array_values(array_unique(array_map(fn(array $period) => $period['tariffId'], $tariffPeriods)));

        return [
            'profileId' => $assignment->getProfile()->getId(),
            'tariffPeriods' => $tariffPeriods,
            'resolvedZones' => $this->resolveZonesForTariffs($tariffPeriods, $rangeStartTimestamp, $rangeEndTimestamp),
            'dynamicPrices' => $this->loadDynamicPrices($tariffIds, $rangeStartTimestamp, $rangeEndTimestamp),
        ];
    }

    private function normalizeTariffPeriods(array $tariffPeriods): array {
        $normalized = [];
        foreach ($tariffPeriods as $tariffPeriod) {
            if (!$tariffPeriod instanceof EnergyTariffProfileTariffPeriod || !$tariffPeriod->getTariff()) {
                continue;
            }

            $normalizedPricePeriods = [];
            foreach ($tariffPeriod->getPricePeriods() as $pricePeriod) {
                if (!$pricePeriod instanceof EnergyTariffProfilePricePeriod) {
                    continue;
                }
                $items = [];
                foreach ($pricePeriod->getItems() as $item) {
                    if (!$item instanceof EnergyTariffProfilePriceItem || $item->getUnit() !== EnergyPriceUnit::KWH) {
                        continue;
                    }
                    $items[] = [
                        'componentCode' => $item->getComponentCode()->value,
                        'zoneCode' => $item->getZoneCode(),
                        'amount' => (float)$item->getAmount(),
                        'unit' => $item->getUnit()->value,
                    ];
                }

                usort($items, fn(array $left, array $right) => $left['componentCode'] <=> $right['componentCode']);

                $normalizedPricePeriods[] = [
                    'id' => (int)$pricePeriod->getId(),
                    'currency' => $pricePeriod->getCurrency(),
                    'validFrom' => $pricePeriod->getValidFrom(),
                    'validTo' => $pricePeriod->getValidTo(),
                    'startTs' => $pricePeriod->getValidFrom()?->getTimestamp() ?? PHP_INT_MIN,
                    'endTs' => $pricePeriod->getValidTo()?->getTimestamp() ?? PHP_INT_MAX,
                    'billingPeriodLength' => $pricePeriod->getBillingPeriodLength(),
                    'billingPeriodUnit' => $pricePeriod->getBillingPeriodUnit()->value,
                    'items' => $items,
                ];
            }

            usort($normalizedPricePeriods, fn(array $left, array $right) => $left['startTs'] <=> $right['startTs']);

            $normalized[] = [
                'id' => (int)$tariffPeriod->getId(),
                'tariffId' => (int)$tariffPeriod->getTariff()->getId(),
                'tariff' => $tariffPeriod->getTariff(),
                'isDynamic' => $tariffPeriod->getTariff()->isDynamic(),
                'aggregationPeriodMinutes' => $tariffPeriod->getTariff()->getAggregationPeriodMinutes(),
                'timezone' => $tariffPeriod->getTariff()->getConfig()['timezone'] ?? 'UTC',
                'validFrom' => $tariffPeriod->getValidFrom(),
                'validTo' => $tariffPeriod->getValidTo(),
                'startTs' => $tariffPeriod->getValidFrom()?->getTimestamp() ?? PHP_INT_MIN,
                'endTs' => $tariffPeriod->getValidTo()?->getTimestamp() ?? PHP_INT_MAX,
                'pricePeriods' => $normalizedPricePeriods,
            ];
        }

        usort($normalized, fn(array $left, array $right) => $left['startTs'] <=> $right['startTs']);
        return $normalized;
    }

    private function resolveZonesForTariffs(array $tariffPeriods, int $rangeStartTimestamp, int $rangeEndTimestamp): array {
        $normalized = [];
        $periodStart = new \DateTime('@' . $rangeStartTimestamp);
        $periodEnd = new \DateTime('@' . $rangeEndTimestamp);
        $periodStart->setTimezone(new \DateTimeZone('UTC'));
        $periodEnd->setTimezone(new \DateTimeZone('UTC'));
        foreach ($tariffPeriods as $tariffPeriod) {
            if ($tariffPeriod['isDynamic'] || isset($normalized[$tariffPeriod['tariffId']])) {
                continue;
            }
            $normalized[$tariffPeriod['tariffId']] = $this->tariffZoneResolver->resolveIntervals(
                $tariffPeriod['tariff'],
                clone $periodStart,
                clone $periodEnd
            );
        }

        return $normalized;
    }

    private function loadDynamicPrices(array $tariffIds, int $rangeStartTimestamp, int $rangeEndTimestamp): array {
        if (!$tariffIds) {
            return [];
        }

        $normalized = [];
        $rows = $this->measurementLogsEntityManager->getConnection()->executeQuery(
            'SELECT tariff_id, component_code, date_from, currency, amount
                FROM supla_energy_tariff_dynamic_price
                WHERE tariff_id IN (:tariffIds)
                    AND date_to > :rangeStart
                    AND date_from < :rangeEnd',
            [
                'tariffIds' => $tariffIds,
                'rangeStart' => DateUtils::timestampToMysqlUtc($rangeStartTimestamp),
                'rangeEnd' => DateUtils::timestampToMysqlUtc($rangeEndTimestamp),
            ],
            [
                'tariffIds' => ArrayParameterType::INTEGER,
            ]
        )->fetchAllAssociative();

        foreach ($rows as $row) {
            $normalized[(int)$row['tariff_id']][(new \DateTime($row['date_from'], new \DateTimeZone('UTC')))->getTimestamp()] = [
                'componentCode' => (int)$row['component_code'],
                'amount' => (float)$row['amount'],
                'unit' => EnergyPriceUnit::KWH->value,
                'currency' => $row['currency'],
            ];
        }

        return $normalized;
    }

    private function expandCostRows(array $deltaRows, ?array $context): array {
        $expandedRows = [];

        $deltaRows = $this->aggregateDeltaRows($deltaRows, $context);

        foreach ($deltaRows as $deltaRow) {
            $baseRow = [
                'cost_log_key' => $deltaRow['cost_log_key'] ?? ('delta:' . $deltaRow['date_timestamp']),
                'date_timestamp' => $deltaRow['date_timestamp'],
                'slot_start_timestamp' => $deltaRow['slot_start_timestamp'],
                'aggregation_period_minutes' => $deltaRow['aggregation_period_minutes'] ?? 15,
                'date' => $deltaRow['date'],
                'phase1_fae' => $deltaRow['phase1_fae'],
                'phase2_fae' => $deltaRow['phase2_fae'],
                'phase3_fae' => $deltaRow['phase3_fae'],
                'phase1_rae' => $deltaRow['phase1_rae'],
                'phase2_rae' => $deltaRow['phase2_rae'],
                'phase3_rae' => $deltaRow['phase3_rae'],
                'profile_id' => $context['profileId'] ?? null,
                'tariff_id' => null,
                'zone_code' => null,
                'price_period_id' => null,
                'component_code' => null,
                'amount' => null,
                'unit' => EnergyPriceUnit::KWH->value,
                'currency' => null,
                'price_period_valid_from' => null,
                'billing_period_length' => null,
                'billing_period_unit' => null,
                'total_kwh' => $deltaRow['total_kwh'],
                'phase1_kwh' => $deltaRow['phase1_kwh'],
                'phase2_kwh' => $deltaRow['phase2_kwh'],
                'phase3_kwh' => $deltaRow['phase3_kwh'],
                'phase1_reverse_kwh' => $deltaRow['phase1_reverse_kwh'],
                'phase2_reverse_kwh' => $deltaRow['phase2_reverse_kwh'],
                'phase3_reverse_kwh' => $deltaRow['phase3_reverse_kwh'],
                'reverse_kwh' => $deltaRow['reverse_kwh'],
            ];

            if (!$context) {
                $expandedRows[] = $baseRow;
                continue;
            }

            $tariffPeriod = $this->resolveInterval($context['tariffPeriods'], $deltaRow['slot_start_timestamp']);
            if (!$tariffPeriod) {
                $expandedRows[] = $baseRow;
                continue;
            }

            $pricePeriod = $this->resolveInterval($tariffPeriod['pricePeriods'], $deltaRow['slot_start_timestamp']);
            $zoneCode = $tariffPeriod['isDynamic'] ? null : $this->resolveZoneCode(
                $context['resolvedZones'][$tariffPeriod['tariffId']] ?? [],
                $deltaRow['slot_start_timestamp']
            );

            $baseRow['tariff_id'] = $tariffPeriod['tariffId'];
            $baseRow['zone_code'] = $zoneCode;
            $baseRow['price_period_id'] = $pricePeriod['id'] ?? null;
            $baseRow['currency'] = $pricePeriod['currency'] ?? null;
            $baseRow['price_period_valid_from'] = $pricePeriod['validFrom']?->format('Y-m-d H:i:s');
            $baseRow['billing_period_length'] = $pricePeriod['billingPeriodLength'] ?? null;
            $baseRow['billing_period_unit'] = $pricePeriod['billingPeriodUnit'] ?? null;

            if (!$pricePeriod) {
                $expandedRows[] = $baseRow;
                continue;
            }

            $componentRows = $tariffPeriod['isDynamic']
                ? $this->buildDynamicComponentRows($tariffPeriod, $pricePeriod, $deltaRow['slot_start_timestamp'], $context['dynamicPrices'])
                : $this->buildStaticComponentRows($pricePeriod, $zoneCode);

            if (!$componentRows) {
                $expandedRows[] = $baseRow;
                continue;
            }

            foreach ($componentRows as $componentRow) {
                $expandedRows[] = array_merge($baseRow, $componentRow);
            }
        }

        return $expandedRows;
    }

    private function aggregateDeltaRows(array $deltaRows, ?array $context): array {
        if (!$context) {
            return $deltaRows;
        }

        $aggregated = [];
        foreach ($deltaRows as $deltaRow) {
            $tariffPeriod = $this->resolveInterval($context['tariffPeriods'], $deltaRow['slot_start_timestamp']);
            $pricePeriod = $tariffPeriod
                ? $this->resolveInterval($tariffPeriod['pricePeriods'], $deltaRow['slot_start_timestamp'])
                : null;
            $zoneCode = ($tariffPeriod && !$tariffPeriod['isDynamic'])
                ? $this->resolveZoneCode(
                    $context['resolvedZones'][$tariffPeriod['tariffId']] ?? [],
                    $deltaRow['slot_start_timestamp']
                )
                : null;

            if (!$tariffPeriod || $tariffPeriod['isDynamic']) {
                $key = 'delta:' . $deltaRow['date_timestamp'];
                $deltaRow['aggregation_period_minutes'] = 15;
            } else {
                $deltaRow['aggregation_period_minutes'] = $tariffPeriod['aggregationPeriodMinutes'];
                $bucketStart = $this->alignTimestamp(
                    $deltaRow['slot_start_timestamp'],
                    $tariffPeriod['aggregationPeriodMinutes'],
                    $tariffPeriod['timezone']
                );
                $key = implode(':', [
                    'bucket',
                    $bucketStart,
                    $tariffPeriod['id'],
                    $pricePeriod['id'] ?? 'none',
                    $zoneCode ?? 'none',
                ]);
            }
            $deltaRow['cost_log_key'] = $key;

            if (!isset($aggregated[$key])) {
                $aggregated[$key] = $deltaRow;
                continue;
            }

            $target = &$aggregated[$key];
            foreach (['phase1_fae', 'phase2_fae', 'phase3_fae', 'phase1_rae', 'phase2_rae', 'phase3_rae'] as $field) {
                $target[$field] = (int)($target[$field] ?? 0) + (int)($deltaRow[$field] ?? 0);
            }
            foreach (
                ['total_kwh', 'phase1_kwh', 'phase2_kwh', 'phase3_kwh', 'reverse_kwh',
                    'phase1_reverse_kwh', 'phase2_reverse_kwh', 'phase3_reverse_kwh'] as $field
            ) {
                $target[$field] += $deltaRow[$field];
            }
            if ($deltaRow['date_timestamp'] > $target['date_timestamp']) {
                $target['date_timestamp'] = $deltaRow['date_timestamp'];
                $target['date'] = $deltaRow['date'];
            }
            $target['slot_start_timestamp'] = min($target['slot_start_timestamp'], $deltaRow['slot_start_timestamp']);
            unset($target);
        }

        return array_values($aggregated);
    }

    private function alignTimestamp(int $timestamp, int $periodMinutes, string $timezone): int {
        $local = (new \DateTimeImmutable('@' . $timestamp))->setTimezone(new \DateTimeZone($timezone));
        $minutesSinceMidnight = ((int)$local->format('G') * 60) + (int)$local->format('i');
        $bucketMinute = intdiv($minutesSinceMidnight, $periodMinutes) * $periodMinutes;
        return $local->setTime(0, 0)->modify(sprintf('+%d minutes', $bucketMinute))->getTimestamp();
    }

    private function resolveInterval(array $intervals, int $timestamp): ?array {
        $left = 0;
        $right = count($intervals) - 1;
        $match = null;

        while ($left <= $right) {
            $middle = intdiv($left + $right, 2);
            $candidate = $intervals[$middle];
            if ($candidate['startTs'] <= $timestamp) {
                $match = $candidate;
                $left = $middle + 1;
            } else {
                $right = $middle - 1;
            }
        }

        if ($match && $timestamp < $match['endTs']) {
            return $match;
        }

        return null;
    }

    private function resolveZoneCode(array $zones, int $timestamp): ?string {
        $zone = $this->resolveInterval($zones, $timestamp);
        return $zone['zoneCode'] ?? null;
    }

    private function buildStaticComponentRows(array $pricePeriod, ?string $zoneCode): array {
        $rows = [];
        foreach ($pricePeriod['items'] as $item) {
            if ($item['zoneCode'] !== null && $item['zoneCode'] !== $zoneCode) {
                continue;
            }
            $rows[] = [
                'component_code' => $item['componentCode'],
                'amount' => $item['amount'],
                'unit' => $item['unit'],
                'currency' => $pricePeriod['currency'],
            ];
        }

        usort($rows, fn(array $left, array $right) => $left['component_code'] <=> $right['component_code']);
        return $rows;
    }

    private function buildDynamicComponentRows(array $tariffPeriod, array $pricePeriod, int $slotStartTimestamp, array $dynamicPrices): array {
        $rows = [];
        $dynamicPrice = $dynamicPrices[$tariffPeriod['tariffId']][$slotStartTimestamp] ?? null;
        if ($dynamicPrice) {
            $rows[] = [
                'component_code' => $dynamicPrice['componentCode'],
                'amount' => $dynamicPrice['amount'],
                'unit' => $dynamicPrice['unit'],
                'currency' => $dynamicPrice['currency'],
            ];
        }

        foreach ($pricePeriod['items'] as $item) {
            $rows[] = [
                'component_code' => $item['componentCode'],
                'amount' => $item['amount'],
                'unit' => $item['unit'],
                'currency' => $pricePeriod['currency'],
            ];
        }

        usort($rows, fn(array $left, array $right) => $left['component_code'] <=> $right['component_code']);
        return $rows;
    }
}
