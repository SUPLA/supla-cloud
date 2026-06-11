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

namespace App\Command\Cyclic;

use App\Entity\EntityUtils;
use App\Entity\MeasurementLogs\ElectricityMeterDeltaLogItem;
use App\Entity\MeasurementLogs\ElectricityMeterLogItem;
use App\Model\Transactional;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ElectricityMeterLogsCalculateDeltasCommand extends AbstractCyclicCommand {
    use Transactional;

    private const DEFAULT_BATCH_SIZE = 1000;

    public function __construct(private readonly EntityManagerInterface $measurementLogsEntityManager) {
        parent::__construct();
    }

    protected function configure(): void {
        $this
            ->setHidden(true)
            ->setName('supla:cyclic:electricity-meter-logs-calculate-deltas')
            ->setDescription('Calculates deltas for electricity meter logs.')
            ->addOption('batch-size', null, InputOption::VALUE_REQUIRED, 'Maximum number of logs to process per channel at once.', self::DEFAULT_BATCH_SIZE);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int {
        $channelsWithLogs = $this->measurementLogsEntityManager->createQueryBuilder()
            ->select('DISTINCT l.channel_id')
            ->from(ElectricityMeterLogItem::class, 'l')
            ->getQuery()
            ->getScalarResult();

        foreach ($channelsWithLogs as $row) {
            $channelId = $row['channel_id'];

            $lastDelta = $this->measurementLogsEntityManager->createQueryBuilder()
                ->select('d')
                ->from(ElectricityMeterDeltaLogItem::class, 'd')
                ->where('d.channel_id = :channelId')
                ->setParameter('channelId', $channelId)
                ->orderBy('d.date', 'DESC')
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult();

            $startDate = $lastDelta ? new \DateTime($lastDelta->getDate(), new \DateTimeZone('UTC')) : null;

            $qb = $this->measurementLogsEntityManager->createQueryBuilder()
                ->select('l')
                ->from(ElectricityMeterLogItem::class, 'l')
                ->where('l.channel_id = :channelId')
                ->setParameter('channelId', $channelId)
                ->orderBy('l.date', 'ASC')
                ->setMaxResults((int)$input->getOption('batch-size'));

            if ($startDate) {
                $precedingLog = $this->measurementLogsEntityManager->createQueryBuilder()
                    ->select('l')
                    ->from(ElectricityMeterLogItem::class, 'l')
                    ->where('l.channel_id = :channelId')
                    ->andWhere('l.date <= :startDate')
                    ->setParameter('channelId', $channelId)
                    ->setParameter('startDate', $startDate->format('Y-m-d H:i:s'))
                    ->orderBy('l.date', 'DESC')
                    ->setMaxResults(1)
                    ->getQuery()
                    ->getOneOrNullResult();

                $qb->andWhere('l.date > :startDate')
                    ->setParameter('startDate', $startDate->format('Y-m-d H:i:s'));

                $logs = $qb->getQuery()->getResult();
                if ($precedingLog) {
                    array_unshift($logs, $precedingLog);
                }
            } else {
                $logs = $qb->getQuery()->getResult();
            }

            if (count($logs) < 2) {
                continue;
            }

            $this->calculateDeltasForChannel($channelId, $logs, $startDate);
            $this->measurementLogsEntityManager->flush();
            $this->measurementLogsEntityManager->clear();
        }

        return self::SUCCESS;
    }

    /**
     * @param int $channelId
     * @param ElectricityMeterLogItem[] $logs
     * @param \DateTime|null $startDate
     */
    private function calculateDeltasForChannel(int $channelId, array $logs, ?\DateTime $startDate): void {
        $firstLogDate = new \DateTime($logs[0]->getDate(), new \DateTimeZone('UTC'));

        // Target dates should be :00, :15, :30, :45
        $currentSlotDate = clone($startDate ?: $firstLogDate);
        if (!$startDate) {
            $minutes = (int)$currentSlotDate->format('i');
            $seconds = (int)$currentSlotDate->format('s');
            $next15 = (int)(ceil(($minutes + $seconds / 60.0 + 0.0001) / 15) * 15);
            if ($next15 === 60) {
                $currentSlotDate->modify("+1 hour");
                $currentSlotDate->setTime((int)$currentSlotDate->format('H'), 0, 0);
            } else {
                $currentSlotDate->setTime((int)$currentSlotDate->format('H'), $next15, 0);
            }
        } else {
            $currentSlotDate->modify("+15 minutes");
        }

        $lastLog = end($logs);
        $lastLogDate = new \DateTime($lastLog->getDate(), new \DateTimeZone('UTC'));

        $logIndex = 0;
        $lastLogTimestamp = $lastLogDate->getTimestamp();
        while ($currentSlotDate->getTimestamp() <= $lastLogTimestamp) {
            // Find logs that surround $currentSlotDate
            while ($logIndex < count($logs) - 1 && (new \DateTime($logs[$logIndex + 1]->getDate(), new \DateTimeZone('UTC')))->getTimestamp() < $currentSlotDate->getTimestamp()) {
                $logIndex++;
            }

            if ($logIndex >= count($logs) - 1) {
                break;
            }

            $logB = $logs[$logIndex + 1];
            $logA = $logs[$logIndex];

            $prevSlotDate = clone $currentSlotDate;
            $prevSlotDate->modify("-15 minutes");

            $logIndexA = $logIndex;
            while ($logIndexA > 0 && new \DateTime($logs[$logIndexA]->getDate(), new \DateTimeZone('UTC')) > $prevSlotDate) {
                $logIndexA--;
            }
            $logAForA = $logs[$logIndexA];
            $logBForA = $logs[$logIndexA + 1];

            $valA = $this->estimateValuesAt($logAForA, $logBForA, $prevSlotDate);
            $valB = $this->estimateValuesAt($logA, $logB, $currentSlotDate);

            if ($valA === null || $valB === null || $prevSlotDate->getTimestamp() < $firstLogDate->getTimestamp()) {
                $currentSlotDate->modify("+15 minutes");
                continue;
            }

            $delta = new ElectricityMeterDeltaLogItem($channelId, $currentSlotDate->format('Y-m-d H:i:s'));

            foreach (['phase1_fae', 'phase1_rae', 'phase2_fae', 'phase2_rae', 'phase3_fae', 'phase3_rae'] as $field) {
                $v = $valB[$field] - $valA[$field];
                if ($v < 0) {
                    // Counter reset detected. We don't know the exact reset point or max value.
                    $v = $valB[$field];
                }
                EntityUtils::setField($delta, $field, (int)round($v));
            }

            $this->measurementLogsEntityManager->persist($delta);

            $currentSlotDate->modify("+15 minutes");
        }
    }

    private function estimateValuesAt(ElectricityMeterLogItem $logA, ElectricityMeterLogItem $logB, \DateTime $targetDate): ?array {
        $dateA = new \DateTime($logA->getDate(), new \DateTimeZone('UTC'));
        $dateB = new \DateTime($logB->getDate(), new \DateTimeZone('UTC'));

        $tA = $dateA->getTimestamp();
        $tB = $dateB->getTimestamp();
        $tT = $targetDate->getTimestamp();

        $ratio = ($tB === $tA) ? 0 : ($tT - $tA) / ($tB - $tA);

        $values = [];
        foreach (['phase1_fae', 'phase1_rae', 'phase2_fae', 'phase2_rae', 'phase3_fae', 'phase3_rae'] as $field) {
            $valA = EntityUtils::getField($logA, $field) ?: 0;
            $valB = EntityUtils::getField($logB, $field) ?: 0;
            $v = $valA + $ratio * ($valB - $valA);
            $values[$field] = $v;
        }

        return $values;
    }

    protected function getIntervalInMinutes(): int {
        return 15;
    }
}
