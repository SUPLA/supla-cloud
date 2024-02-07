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
use SuplaBundle\Entity\MeasurementLogs\ElectricityMeterLogItem;
use SuplaBundle\Entity\MeasurementLogs\ElectricityMeterVoltageAberrationLogItem;
use SuplaBundle\Entity\MeasurementLogs\ImpulseCounterLogItem;
use SuplaBundle\Entity\MeasurementLogs\TemperatureLogItem;
use SuplaBundle\Entity\MeasurementLogs\TempHumidityLogItem;
use SuplaBundle\Entity\MeasurementLogs\ThermostatLogItem;
use SuplaBundle\Model\TimeProvider;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DeleteOrphanedMeasurementLogsCommand extends Command implements CyclicCommand {
    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct($measurementLogsEntityManager) {
        parent::__construct();
        $this->entityManager = $measurementLogsEntityManager;
    }

    protected function configure() {
        $this
            ->setName('supla:clean:orphaned-measurement-logs')
            ->setDescription('Delete logs of channels that has been deleted.');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $this->logClean($output, ElectricityMeterLogItem::class);
        $this->logClean($output, ElectricityMeterVoltageAberrationLogItem::class);
        $this->logClean($output, ImpulseCounterLogItem::class);
        $this->logClean($output, TemperatureLogItem::class);
        $this->logClean($output, TempHumidityLogItem::class);
        $this->logClean($output, ThermostatLogItem::class);
        return 0;
    }

    protected function logClean(OutputInterface $output, string $entity): void {
        $sql = "DELETE t FROM `" . $this->entityManager->getClassMetadata($entity)->getTableName()
            . "` AS t LEFT JOIN supla_dev_channel AS c ON c.id = t.channel_id WHERE c.id IS NULL";
        $stmt = $this->entityManager->getConnection()->prepare($sql);
        $rowCount = $stmt->executeStatement();
        if ($rowCount || $output->isVerbose()) {
            $className = basename(str_replace('\\', '/', $entity));
            $output->writeln(sprintf('Deleted <info>%d</info> items from <comment>%s</comment> storage.', $rowCount, $className));
        }
    }

    public function shouldRunNow(TimeProvider $timeProvider): bool {
        return date('H:i', $timeProvider->getTimestamp()) === '01:20';
    }
}
