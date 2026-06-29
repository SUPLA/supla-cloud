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

use App\Command\Initialization\InitializationCommand;
use App\Entity\EntityUtils;
use App\Entity\MeasurementLogs\ElectricityMeterDeltaLogItem;
use App\Entity\MeasurementLogs\ElectricityMeterLogItem;
use App\Model\Transactional;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Lock\LockFactory;

class ElectricityMeterLogsCalculateDeltasCommand extends AbstractCyclicCommand implements InitializationCommand {
    use Transactional;

    private const DEFAULT_BATCH_SIZE = 10000;
    private const SLOT_DURATION_IN_SECONDS = 900;
    private const DELTA_FIELDS = ['phase1_fae', 'phase1_rae', 'phase2_fae', 'phase2_rae', 'phase3_fae', 'phase3_rae'];

    public function __construct(
        private readonly EntityManagerInterface $measurementLogsEntityManager,
        private readonly LockFactory $lockFactory,
    ) {
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
        $lock = $this->lockFactory->createLock('supla-electricity-meter-logs-calculate-deltas');
        if (!$lock->acquire()) {
            $output->writeln("The command is already running.");
            return 0;
        }

        try {
            $channelsWithLogs = $this->measurementLogsEntityManager->createQueryBuilder()
                ->select('DISTINCT l.channel_id')
                ->from(ElectricityMeterLogItem::class, 'l')
                ->getQuery()
                ->getScalarResult();

            foreach ($channelsWithLogs as $row) {
                $channelId = $row['channel_id'];

                if ($output->isVerbose()) {
                    $output->writeln("Processing channel ID: $channelId");
                }

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

                if ($output->isVerbose()) {
                    $output->writeln("  Fetched " . count($logs) . " logs for processing");
                }

                if (count($logs) < 2) {
                    if ($output->isVerbose()) {
                        $output->writeln("  Skipping channel $channelId - insufficient logs (less than 2)");
                    }
                    continue;
                }

                $this->calculateDeltasForChannel($channelId, $logs, $startDate, $output);
                if ($output->isVerbose()) {
                    $output->writeln("  Flushing changes to database");
                }
                $this->measurementLogsEntityManager->flush();
                $this->measurementLogsEntityManager->clear();
            }
        } finally {
            $lock->release();
        }

        return self::SUCCESS;
    }

    /**
     * @param int $channelId
     * @param ElectricityMeterLogItem[] $logs
     * @param \DateTime|null $startDate
     */
    private function calculateDeltasForChannel(int $channelId, array $logs, ?\DateTime $startDate, OutputInterface $output): void {
        $normalizedLogs = $this->normalizeLogs($logs);
        $firstLogDate = new \DateTime($normalizedLogs[0]['date'], new \DateTimeZone('UTC'));
        $currentSlotDate = $this->getFirstSlotDate($firstLogDate, $startDate);
        $lastLog = end($normalizedLogs);
        $lastLogDate = new \DateTime($lastLog['date'], new \DateTimeZone('UTC'));

        $firstSlotTimestamp = $currentSlotDate->getTimestamp();
        $firstLogTimestamp = $firstLogDate->getTimestamp();
        $lastLogTimestamp = $lastLogDate->getTimestamp();

        if ($firstSlotTimestamp > $lastLogTimestamp) {
            return;
        }

        $currentSlotEndTimestamp = null;
        $currentSlotTotals = $this->createEmptySlotTotals();

        for ($i = 0, $logsCount = count($normalizedLogs) - 1; $i < $logsCount; $i++) {
            $logA = $normalizedLogs[$i];
            $logB = $normalizedLogs[$i + 1];
            $tA = $logA['timestamp'];
            $tB = $logB['timestamp'];

            if ($tB <= $tA) {
                continue;
            }

            $slotEndTimestamp = intdiv($tA, self::SLOT_DURATION_IN_SECONDS) * self::SLOT_DURATION_IN_SECONDS + self::SLOT_DURATION_IN_SECONDS;
            while ($slotEndTimestamp <= $lastLogTimestamp && $slotEndTimestamp - self::SLOT_DURATION_IN_SECONDS < $tB) {
                $slotStartTimestamp = $slotEndTimestamp - self::SLOT_DURATION_IN_SECONDS;
                if ($slotEndTimestamp >= $firstSlotTimestamp && $slotStartTimestamp >= $firstLogTimestamp) {
                    if ($currentSlotEndTimestamp === null) {
                        $currentSlotEndTimestamp = $slotEndTimestamp;
                    }

                    while ($currentSlotEndTimestamp < $slotEndTimestamp) {
                        $this->persistDelta($channelId, $currentSlotEndTimestamp, $currentSlotTotals);
                        $currentSlotEndTimestamp += self::SLOT_DURATION_IN_SECONDS;
                        $currentSlotTotals = $this->createEmptySlotTotals();
                    }

                    $overlapStart = max($slotStartTimestamp, $tA);
                    $overlapEnd = min($slotEndTimestamp, $tB);

                    if ($overlapStart < $overlapEnd) {
                        $intervalDuration = $tB - $tA;
                        $overlapRatio = ($overlapEnd - $overlapStart) / $intervalDuration;
                        foreach (self::DELTA_FIELDS as $field) {
                            $valA = $logA[$field];
                            $valB = $logB[$field];
                            if ($valB >= $valA) {
                                $currentSlotTotals[$field] += ($valB - $valA) * $overlapRatio;
                            }
                        }
                    }
                }

                $slotEndTimestamp += self::SLOT_DURATION_IN_SECONDS;
            }
        }

        if ($currentSlotEndTimestamp !== null) {
            $this->persistDelta($channelId, $currentSlotEndTimestamp, $currentSlotTotals);
        }
    }

    /**
     * @param ElectricityMeterLogItem[] $logs
     */
    private function normalizeLogs(array $logs): array {
        $normalizedLogs = [];
        foreach ($logs as $log) {
            $normalizedLog = [
                'date' => $log->getDate(),
                'timestamp' => (new \DateTime($log->getDate(), new \DateTimeZone('UTC')))->getTimestamp(),
            ];
            foreach (self::DELTA_FIELDS as $field) {
                $normalizedLog[$field] = EntityUtils::getField($log, $field) ?: 0;
            }
            $normalizedLogs[] = $normalizedLog;
        }

        return $normalizedLogs;
    }

    private function createEmptySlotTotals(): array {
        return array_fill_keys(self::DELTA_FIELDS, 0.0);
    }

    private function persistDelta(int $channelId, int $slotEndTimestamp, array $fields): void {
        $delta = new ElectricityMeterDeltaLogItem(
            $channelId,
            gmdate('Y-m-d H:i:s', $slotEndTimestamp),
            (int)round($fields['phase1_fae']),
            (int)round($fields['phase1_rae']),
            (int)round($fields['phase2_fae']),
            (int)round($fields['phase2_rae']),
            (int)round($fields['phase3_fae']),
            (int)round($fields['phase3_rae']),
        );
        $this->measurementLogsEntityManager->persist($delta);
    }

    private function getFirstSlotDate(\DateTime $firstLogDate, ?\DateTime $startDate): \DateTime {
        $currentSlotDate = clone($startDate ?: $firstLogDate);
        if ($startDate) {
            $currentSlotDate->modify('+15 minutes');
            return $currentSlotDate;
        }

        $minutes = (int)$currentSlotDate->format('i');
        $seconds = (int)$currentSlotDate->format('s');
        $next15 = (int)(ceil(($minutes + $seconds / 60.0 + 0.0001) / 15) * 15);
        if ($next15 === 60) {
            $currentSlotDate->modify('+1 hour');
            $currentSlotDate->setTime((int)$currentSlotDate->format('H'), 0, 0);
        } else {
            $currentSlotDate->setTime((int)$currentSlotDate->format('H'), $next15, 0);
        }

        return $currentSlotDate;
    }

    protected function getIntervalInMinutes(): int {
        return 15;
    }
}
