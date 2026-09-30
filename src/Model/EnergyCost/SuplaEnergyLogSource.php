<?php
/*
 Copyright (C) AC SOFTWARE SP. Z O.O.

 This program is free software; you can redistribute it and/or
 modify it under the terms of the GNU General Public License
 as published by the Free Software Foundation; either version 2
 of the License, or (at your option) any later version.
 */

namespace App\Model\EnergyCost;

use DateTimeImmutable;
use DateTimeZone;
use Doctrine\DBAL\ParameterType;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Supla\EnergyCostCalculator\Contract\EnergyLogSource;
use Supla\EnergyCostCalculator\Model\EnergyLog;

final class SuplaEnergyLogSource implements EnergyLogSource {
    private const FIELDS = [
        'phase1_fae', 'phase1_rae', 'phase1_fre', 'phase1_rre',
        'phase2_fae', 'phase2_rae', 'phase2_fre', 'phase2_rre',
        'phase3_fae', 'phase3_rae', 'phase3_fre', 'phase3_rre',
        'fae_balanced', 'rae_balanced',
    ];

    public function __construct(private readonly EntityManagerInterface $measurementLogsEntityManager) {
    }

    public function getLogs(string $meterId, ?DateTimeImmutable $after, int $limit): iterable {
        if (!ctype_digit($meterId)) {
            throw new InvalidArgumentException("Invalid electricity meter ID: $meterId");
        }
        if ($limit < 1) {
            throw new InvalidArgumentException('Log limit must be positive.');
        }

        $utc = new DateTimeZone('UTC');
        $connection = $this->measurementLogsEntityManager->getConnection();
        $parameters = ['channelId' => (int)$meterId];
        $types = ['channelId' => ParameterType::INTEGER];
        $where = 'channel_id = :channelId';
        if ($after !== null) {
            $where .= ' AND date > :after';
            $parameters['after'] = $after->setTimezone($utc)->format('Y-m-d H:i:s');
        }
        $rows = $connection->executeQuery(
            'SELECT date, ' . implode(', ', self::FIELDS) . " FROM supla_em_log WHERE $where ORDER BY date ASC LIMIT $limit",
            $parameters,
            $types
        )->fetchAllAssociative();

        if ($after !== null) {
            $baseline = $connection->executeQuery(
                'SELECT date, ' . implode(', ', self::FIELDS) . ' FROM supla_em_log
                 WHERE channel_id = :channelId AND date <= :after ORDER BY date DESC LIMIT 1',
                [
                    'channelId' => (int)$meterId,
                    'after' => $after->setTimezone($utc)->format('Y-m-d H:i:s'),
                ],
                ['channelId' => ParameterType::INTEGER]
            )->fetchAssociative();
            if ($baseline !== false) {
                array_unshift($rows, $baseline);
            }
        }

        foreach ($rows as $row) {
            $values = [];
            foreach (self::FIELDS as $field) {
                $values[$field] = $row[$field] === null ? null : (int)$row[$field];
            }
            yield new EnergyLog(new DateTimeImmutable((string)$row['date'], $utc), $values);
        }
    }
}
