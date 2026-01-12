<?php
namespace SuplaBundle\Command\Cyclic;

use Doctrine\Persistence\ManagerRegistry;
use SuplaBundle\Command\CopyMeasurementLogsCommand;
use SuplaBundle\Utils\DatabaseUtils;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class MoveNewMeasurementLogsCommand extends AbstractCyclicCommand {
    public function __construct(private ManagerRegistry $registry) {
        parent::__construct();
    }

    protected function configure() {
        $this
            ->setName('supla:cyclic:move-new-measurement-logs')
            ->setDescription('Copies new measurement logs from MariaDB to TSDB if exist.')
            ->addOption('batch-size', null, InputOption::VALUE_REQUIRED, 'Number of records to process in one batch', 100)
            ->addOption('memory-limit', null, InputOption::VALUE_REQUIRED, 'Memory limit for the script', '2G')
            ->setHidden(true);
    }

    /** @inheritdoc */
    protected function execute(InputInterface $input, OutputInterface $output) {
        $io = new SymfonyStyle($input, $output);
        $emMariadb = $this->registry->getManager('default');
        $emTsdb = $this->registry->getManager('tsdb');
        $emLogs = $this->registry->getManager('measurement_logs');
        if (DatabaseUtils::getPlatform($emLogs) !== DatabaseUtils::PSQL) {
            $io->warning('Measurement logs database is not TSDB. Skipping copy operation.');
            return 0;
        }

        ini_set('memory_limit', $input->getOption('memory-limit'));
        $this->getApplication()->setAutoExit(false);
        $batchSize = (int)$input->getOption('batch-size');

        foreach (CopyMeasurementLogsCommand::LOG_TABLES as $tableName) {
            $dateColumnName = $tableName === 'supla_energy_price_log' ? 'date_from' : 'date';
            $query = "SELECT * FROM $tableName 
                      WHERE $dateColumnName > DATE_SUB(NOW(), INTERVAL 3 DAY) 
                      ORDER BY $dateColumnName ASC 
                      LIMIT $batchSize";
            $rows = $emMariadb->getConnection()
                ->executeQuery($query)
                ->fetchAllAssociative();

            if ($rows) {
                $io->writeln('Moving ' . count($rows) . ' logs from ' . $tableName . '.');
                CopyMeasurementLogsCommand::insertIgnoreLogsIntoTsdb($rows, $tableName, $emTsdb);
                $primaryKeyColumns = CopyMeasurementLogsCommand::getLogPrimaryKeyColumns($rows[0]);
                foreach ($rows as $row) {
                    $params = [];
                    foreach ($primaryKeyColumns as $column) {
                        $params[] = $row[$column];
                    }
                    $deleteQuery = "DELETE FROM $tableName WHERE " . implode(' = ? AND ', $primaryKeyColumns) . ' = ?';
                    $emMariadb->getConnection()->executeQuery($deleteQuery, $params);
                    $output->writeln('Done!');
                }
            }
        }

        return 0;
    }

    protected function getIntervalInMinutes(): int {
        return 60;
    }
}
