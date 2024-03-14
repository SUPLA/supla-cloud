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

namespace SuplaBundle\Command\Cyclic;

use Doctrine\ORM\EntityManagerInterface;
use SuplaBundle\Entity\MeasurementLogs\ElectricityMeterCurrentLogItem;
use SuplaBundle\Entity\MeasurementLogs\ElectricityMeterPowerActiveLogItem;
use SuplaBundle\Entity\MeasurementLogs\ElectricityMeterVoltageAberrationLogItem;
use SuplaBundle\Entity\MeasurementLogs\ElectricityMeterVoltageLogItem;
use SuplaBundle\Model\TimeProvider;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DeleteOldMeasurementLogsCommand extends Command implements CyclicCommand {
    private EntityManagerInterface $entityManager;
    private array $logsRetentionConfig;

    public function __construct($measurementLogsEntityManager, array $logsRetentionConfig) {
        parent::__construct();
        $this->entityManager = $measurementLogsEntityManager;
        $this->logsRetentionConfig = $logsRetentionConfig;
    }

    protected function configure() {
        $this
            ->setName('supla:clean:old-measurement-logs')
            ->setDescription('Delete logs of channels that should be deleted due to the retention policy.');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $this->logClean($output, ElectricityMeterVoltageAberrationLogItem::class, $this->logsRetentionConfig['em_voltage_aberrations']);
        $this->logClean($output, ElectricityMeterVoltageLogItem::class, $this->logsRetentionConfig['em_voltage']);
        $this->logClean($output, ElectricityMeterCurrentLogItem::class, $this->logsRetentionConfig['em_current']);
        $this->logClean($output, ElectricityMeterPowerActiveLogItem::class, $this->logsRetentionConfig['em_power_active']);
        return 0;
    }

    protected function logClean(OutputInterface $output, string $entity, int $olderThanDays): void {
        $sql = sprintf(
            'DELETE FROM `%s` WHERE `date` < DATE_SUB(CURRENT_DATE, INTERVAL %d DAY)',
            $this->entityManager->getClassMetadata($entity)->getTableName(),
            $olderThanDays
        );
        $stmt = $this->entityManager->getConnection()->prepare($sql);
        $rowCount = $stmt->executeStatement();
        if ($rowCount || $output->isVerbose()) {
            $className = basename(str_replace('\\', '/', $entity));
            $output->writeln(sprintf('Deleted <info>%d</info> items from <comment>%s</comment> storage.', $rowCount, $className));
        }
    }

    public function shouldRunNow(TimeProvider $timeProvider): bool {
        return date('H:i', $timeProvider->getTimestamp()) === '02:20';
    }
}
