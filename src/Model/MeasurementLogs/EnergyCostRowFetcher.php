<?php

namespace App\Model\MeasurementLogs;

use App\Entity\MeasurementLogs\EnergyTariffProfileAssignment;
use App\Entity\MeasurementLogs\EnergyTariffProfilePriceItem;
use App\Entity\MeasurementLogs\EnergyTariffProfilePricePeriod;
use App\Entity\MeasurementLogs\EnergyTariffProfileTariffPeriod;
use App\Enums\EnergyPriceUnit;
use App\Utils\DateUtils;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\ORM\EntityManagerInterface;

class EnergyCostRowFetcher {
    public function __construct(
        private readonly EntityManagerInterface $measurementLogsEntityManager,
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
                d.phase3_fae
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
            $row['date_timestamp'] = $dateTimestamp;
            $row['slot_start_timestamp'] = $dateTimestamp - (15 * 60);
            $row['total_kwh'] = ($phase1 + $phase2 + $phase3) / 1000.0;
            $row['phase1_kwh'] = $phase1 / 1000.0;
            $row['phase2_kwh'] = $phase2 / 1000.0;
            $row['phase3_kwh'] = $phase3 / 1000.0;
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
            'resolvedZones' => $this->loadResolvedZones($tariffIds, $rangeStartTimestamp, $rangeEndTimestamp),
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
                'isDynamic' => $tariffPeriod->getTariff()->isDynamic(),
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

    private function loadResolvedZones(array $tariffIds, int $rangeStartTimestamp, int $rangeEndTimestamp): array {
        if (!$tariffIds) {
            return [];
        }

        $normalized = [];
        $rows = $this->measurementLogsEntityManager->getConnection()->executeQuery(
            'SELECT tariff_id, zone_code, period_start, period_end
                FROM supla_energy_tariff_resolved_zone
                WHERE tariff_id IN (:tariffIds)
                    AND period_end > :rangeStart
                    AND period_start < :rangeEnd
                ORDER BY period_start ASC',
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
            $normalized[(int)$row['tariff_id']][] = [
                'zoneCode' => $row['zone_code'],
                'startTs' => (new \DateTime($row['period_start'], new \DateTimeZone('UTC')))->getTimestamp(),
                'endTs' => (new \DateTime($row['period_end'], new \DateTimeZone('UTC')))->getTimestamp(),
            ];
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

        foreach ($deltaRows as $deltaRow) {
            $baseRow = [
                'date_timestamp' => $deltaRow['date_timestamp'],
                'slot_start_timestamp' => $deltaRow['slot_start_timestamp'],
                'date' => $deltaRow['date'],
                'phase1_fae' => $deltaRow['phase1_fae'],
                'phase2_fae' => $deltaRow['phase2_fae'],
                'phase3_fae' => $deltaRow['phase3_fae'],
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
