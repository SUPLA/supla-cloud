<?php

namespace App\Model\MeasurementLogs;

use App\Utils\DatabaseUtils;
use App\Utils\DateUtils;
use Doctrine\ORM\EntityManagerInterface;

class EnergyCostRowFetcher {
    private readonly string $platform;

    public function __construct(
        private readonly EntityManagerInterface $measurementLogsEntityManager,
        private readonly int $recordLimitPerRequest = 10000,
    ) {
        $this->platform = DatabaseUtils::getPlatform($this->measurementLogsEntityManager);
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
        $order = $orderDesc ? 'DESC' : 'ASC';
        $slotStartExpr = $this->platform === DatabaseUtils::PSQL
            ? "b.date - INTERVAL '15 minutes'"
            : 'DATE_SUB(b.date, INTERVAL 15 MINUTE)';
        $dateTsExpr = $this->platform === DatabaseUtils::PSQL ? 'EXTRACT(EPOCH FROM b.date)::INTEGER' : 'UNIX_TIMESTAMP(b.date)';
        $slotStartTsExpr = $this->platform === DatabaseUtils::PSQL ? "EXTRACT(EPOCH FROM ($slotStartExpr))::INTEGER" : "UNIX_TIMESTAMP($slotStartExpr)";

        $where = 'WHERE d.channel_id = :channelId ';
        if ($afterTimestamp > 0) {
            $where .= 'AND d.date > :afterDate ';
        }
        if ($beforeTimestamp > 0) {
            $where .= 'AND d.date < :beforeDate ';
        }

        $sql = "SELECT $dateTsExpr date_timestamp,
                $slotStartTsExpr slot_start_timestamp,
                b.date,
                b.phase1_fae,
                b.phase2_fae,
                b.phase3_fae,
                pa.profile_id,
                tp.tariff_id,
                rz.zone_code,
                pp.id price_period_id,
                ppi.component_code,
                ppi.amount,
                ppi.unit,
                pp.currency,
                pp.valid_from price_period_valid_from,
                pp.billing_period_length,
                pp.billing_period_unit,
                ((COALESCE(b.phase1_fae, 0) + COALESCE(b.phase2_fae, 0) + COALESCE(b.phase3_fae, 0)) / 1000.0) total_kwh,
                (COALESCE(b.phase1_fae, 0) / 1000.0) phase1_kwh,
                (COALESCE(b.phase2_fae, 0) / 1000.0) phase2_kwh,
                (COALESCE(b.phase3_fae, 0) / 1000.0) phase3_kwh
            FROM (
                SELECT d.channel_id, d.date, d.phase1_fae, d.phase2_fae, d.phase3_fae
                FROM supla_em_delta_log d
                $where
                ORDER BY d.date $order
                LIMIT :limit OFFSET :offset
            ) b
            LEFT JOIN supla_energy_tariff_profile_assignment pa
                ON pa.channel_id = b.channel_id
            LEFT JOIN supla_energy_tariff_profile_tariff_period tp
                ON tp.profile_id = pa.profile_id
                AND (tp.valid_from IS NULL OR $slotStartExpr >= tp.valid_from)
                AND (tp.valid_to IS NULL OR $slotStartExpr < tp.valid_to)
            LEFT JOIN supla_energy_tariff_resolved_zone rz
                ON rz.tariff_id = tp.tariff_id
                AND $slotStartExpr >= rz.period_start
                AND $slotStartExpr < rz.period_end
            LEFT JOIN supla_energy_tariff_profile_price_period pp
                ON pp.tariff_period_id = tp.id
                AND (pp.valid_from IS NULL OR $slotStartExpr >= pp.valid_from)
                AND (pp.valid_to IS NULL OR $slotStartExpr < pp.valid_to)
            LEFT JOIN supla_energy_tariff_profile_price_item ppi
                ON ppi.price_period_id = pp.id
                AND ppi.unit = 'kWh'
                AND (ppi.zone_code = rz.zone_code OR ppi.zone_code IS NULL)
            ORDER BY b.date $order, ppi.component_code ASC";

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

        return $stmt->executeQuery()->fetchAllAssociative();
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
}
