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
use Doctrine\Persistence\ManagerRegistry;
use SuplaBundle\Command\CopyMeasurementLogsCommand;
use SuplaBundle\Model\TimeProvider;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class DeleteOrphanedMeasurementLogsCommand extends Command implements CyclicCommand {
    public function __construct(private readonly ManagerRegistry $registry) {
        parent::__construct();
    }

    protected function configure() {
        $this
            ->setName('supla:clean:orphaned-measurement-logs')
            ->setDescription('Delete logs of channels that has been deleted.');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $channelIds = $this->getExistingChannelIds();
        $logsManager = $this->registry->getManager('measurement_logs');
        $io = new SymfonyStyle($input, $output);
        foreach (CopyMeasurementLogsCommand::LOG_TABLES as $logTable) {
            $this->logClean($logsManager, $channelIds, $logTable, $io);
        }
        return 0;
    }

    private function getExistingChannelIds(): array {
        $emMariadb = $this->registry->getManager('default');
        $ids = $emMariadb->getConnection()->executeQuery('SELECT id FROM supla_dev_channel')->fetchFirstColumn();
        return $ids;
    }

    protected function logClean(EntityManagerInterface $em, array $existingChannelIds, string $logTable, SymfonyStyle $io): void {
        $firstRow = $em->getConnection()->executeQuery(sprintf("SELECT * FROM %s LIMIT 1", $logTable))->fetchAssociative();
        if ($firstRow && array_key_exists('channel_id', $firstRow)) {
            $channelIdsWithLogs = $em->getConnection()
                ->executeQuery(sprintf("SELECT DISTINCT channel_id FROM %s", $logTable))
                ->fetchFirstColumn();
            $extraChannelIds = array_diff($channelIdsWithLogs, $existingChannelIds);
            if ($extraChannelIds) {
                $io->writeln(sprintf('Deleting history from %s, channel IDs: %s', $logTable, implode(', ', $extraChannelIds)));
                $stmt = $em->getConnection()
                    ->prepare(sprintf("DELETE FROM %s WHERE channel_id IN (%s)", $logTable, implode(',', $extraChannelIds)));
                $rowCount = $stmt->executeStatement();
                $io->writeln(sprintf('Deleted <info>%d</info> items from <comment>%s</comment>.', $rowCount, $logTable));
            }
        }
    }

    public function shouldRunNow(TimeProvider $timeProvider): bool {
        return true;//date('H:i', $timeProvider->getTimestamp()) === '01:20';
    }
}
