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
use App\Entity\MeasurementLogs\ElectricityMeterDeltaLogItem;
use App\Entity\MeasurementLogs\ElectricityMeterLogItem;
use App\Model\Transactional;
use Doctrine\ORM\EntityManagerInterface;
use Supla\EnergyCostCalculator\Contract\EnergyLogSource;
use Supla\EnergyCostCalculator\Engine\EnergyLogDeltaCalculator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Lock\LockFactory;

class ElectricityMeterLogsCalculateDeltasCommand extends AbstractCyclicCommand implements InitializationCommand {
    use Transactional;

    private const DEFAULT_BATCH_SIZE = 10000;
    public function __construct(
        private readonly EntityManagerInterface $measurementLogsEntityManager,
        private readonly LockFactory $lockFactory,
        private readonly EnergyLogSource $energyLogSource,
        private readonly EnergyLogDeltaCalculator $energyLogDeltaCalculator,
        private readonly bool $energyLogDeltaCalculationEnabled = true,
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
        if (!$this->energyLogDeltaCalculationEnabled) {
            return self::SUCCESS;
        }
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

                $startDate = $lastDelta ? new \DateTimeImmutable($lastDelta->getDate(), new \DateTimeZone('UTC')) : null;
                $logs = iterator_to_array($this->energyLogSource->getLogs(
                    (string)$channelId,
                    $startDate,
                    (int)$input->getOption('batch-size')
                ));

                $allLogsProcessed = count($logs) <= 2;
                if ($output->isVerbose() && !$allLogsProcessed) {
                    $output->writeln("  Fetched " . count($logs) . " logs for processing");
                }

                if (count($logs) < 2) {
                    if ($output->isVerbose()) {
                        $output->writeln("  Skipping channel $channelId - insufficient logs (less than 2)");
                    }
                    $output->writeln('All logs have been processed.');
                    continue;
                }

                foreach ($this->energyLogDeltaCalculator->calculate($logs, $startDate) as $delta) {
                    $this->persistDelta($channelId, $delta->to->format('Y-m-d H:i:s'), $delta->values);
                }
                if ($output->isVerbose()) {
                    $output->writeln("  Flushing changes to database");
                }
                $this->measurementLogsEntityManager->flush();
                $this->measurementLogsEntityManager->clear();
                if ($allLogsProcessed) {
                    $output->writeln('All logs have been processed.');
                }
            }
        } finally {
            $lock->release();
        }

        return self::SUCCESS;
    }

    /** @param array<string, int> $fields */
    private function persistDelta(int $channelId, string $date, array $fields): void {
        $delta = new ElectricityMeterDeltaLogItem(
            $channelId,
            $date,
            $fields['phase1_fae'] ?? 0,
            $fields['phase1_rae'] ?? 0,
            $fields['phase2_fae'] ?? 0,
            $fields['phase2_rae'] ?? 0,
            $fields['phase1_fre'] ?? 0,
            $fields['phase1_rre'] ?? 0,
            $fields['phase2_fre'] ?? 0,
            $fields['phase2_rre'] ?? 0,
            $fields['phase3_fae'] ?? 0,
            $fields['phase3_rae'] ?? 0,
            $fields['phase3_fre'] ?? 0,
            $fields['phase3_rre'] ?? 0,
            $fields['fae_balanced'] ?? 0,
            $fields['rae_balanced'] ?? 0,
        );
        $this->measurementLogsEntityManager->persist($delta);
    }

    protected function getIntervalInMinutes(): int {
        return 15;
    }
}
