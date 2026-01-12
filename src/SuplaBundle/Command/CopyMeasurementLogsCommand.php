<?php
namespace SuplaBundle\Command;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CopyMeasurementLogsCommand extends Command {
    public const LOG_TABLES = [
        'supla_em_current_log',
        'supla_em_log',
        'supla_em_power_active_log',
        'supla_em_voltage_aberration_log',
        'supla_em_voltage_log',
        'supla_energy_price_log',
        'supla_gp_measurement_log',
        'supla_gp_meter_log',
        'supla_ic_log',
        'supla_temperature_log',
        'supla_temphumidity_log',
        'supla_thermostat_log',
    ];

    public function __construct(private ManagerRegistry $registry) {
        parent::__construct();
    }

    protected function configure() {
        $this
            ->setName('supla:copy-measurement-logs')
            ->setDescription('Copies measurement logs from MariaDB to TSDB.')
            ->addOption('all', null, InputOption::VALUE_NONE, 'Copy all logs (ignore existing data, but do not override them)')
            ->addOption('from-date', null, InputOption::VALUE_REQUIRED, 'Start date for copying logs (Y-m-d format)')
            ->addOption('table', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Comma-separated list of tables to copy')
            ->addOption('batch-size', null, InputOption::VALUE_REQUIRED, 'Number of records to process in one batch', 1000)
            ->addOption('memory-limit', null, InputOption::VALUE_REQUIRED, 'Memory limit for the script', '2G')
            ->setHidden(true);
    }

    /** @inheritdoc */
    protected function execute(InputInterface $input, OutputInterface $output) {
        ini_set('memory_limit', $input->getOption('memory-limit'));
        $io = new SymfonyStyle($input, $output);
        $emMariadb = $this->registry->getManager('default');
        $emTsdb = $this->registry->getManager('tsdb');
        $this->getApplication()->setAutoExit(false);
        $this->getApplication()->run(new StringInput("supla:initialize:tsdb --force"), $output);

        $table = new Table($output);
        $table->setHeaders(['Log table', 'MariaDB Count', 'TSDB Count']);
        $selectedTables = $this->getSelectedTables($input);

        if (!count($selectedTables)) {
            $io->error('No tables selected.');
            return 2;
        }

        foreach ($selectedTables as $tableName) {
            $mariadbCount = $emMariadb->getConnection()
                ->executeQuery("SELECT COUNT(*) count FROM $tableName")
                ->fetchAssociative();

            $tsdbCount = $emTsdb->getConnection()
                ->executeQuery("SELECT COUNT(*) count FROM $tableName")
                ->fetchAssociative();

            $table->addRow([
                $tableName,
                $mariadbCount['count'] ?? 0,
                $tsdbCount['count'] ?? 0,
            ]);
        }
        $table->render();

        if (!$io->confirm('Copy data from MariaDB to TSDB?')) {
            return 0;
        }

        foreach ($selectedTables as $tableName) {
            $output->writeln("\nCopying $tableName...");

            $batchSize = (int)$input->getOption('batch-size');
            $offset = 0;

            $fromDate = null;
            $dateColumnName = $tableName === 'supla_energy_price_log' ? 'date_from' : 'date';
            if ($input->getOption('from-date')) {
                $fromDate = new \DateTime($input->getOption('from-date'));
            } elseif ($input->getOption('all') === false) {
                $latestTsdbDate = $emTsdb->getConnection()
                    ->executeQuery("SELECT MAX($dateColumnName) as max_date FROM $tableName")
                    ->fetchAssociative();
                if ($latestTsdbDate['max_date']) {
                    $fromDate = new \DateTime($latestTsdbDate['max_date']);
                }
            }
            if ($fromDate) {
                $output->writeln("From date: {$fromDate->format('Y-m-d H:i:s')}");
            }

            while (true) {
                $query = "SELECT * FROM $tableName";
                if ($fromDate) {
                    $query .= " WHERE $dateColumnName >= '" . $fromDate->format('Y-m-d H:i:s') . "'";
                }
                $query .= " LIMIT $batchSize OFFSET $offset";

                $rows = $emMariadb->getConnection()
                    ->executeQuery($query)
                    ->fetchAllAssociative();

                if (empty($rows)) {
                    break;
                }

                self::insertIgnoreLogsIntoTsdb($rows, $tableName, $emTsdb);

                $offset += $batchSize;
                if ($offset % ($batchSize * 10) === 0) {
                    $output->write('.');
                }
            }

            $output->writeln('Done!');
        }

        return 0;
    }

    private function getSelectedTables(InputInterface $input): array {
        if ($input->getOption('table')) {
            $selectedTables = $input->getOption('table');
            return array_intersect(self::LOG_TABLES, $selectedTables);
        }
        return self::LOG_TABLES;
    }

    public static function getLogPrimaryKeyColumns(array $sampleLog): array {
        $columns = array_keys($sampleLog);
        $uniqueColumns = [];
        foreach (['channel_id', 'date', 'phase_no', 'date_from'] as $possibleUniqueColumn) {
            if (in_array($possibleUniqueColumn, $columns)) {
                $uniqueColumns[] = $possibleUniqueColumn;
            }
        }
        return $uniqueColumns;
    }

    public static function insertIgnoreLogsIntoTsdb(array $rows, string $tableName, ObjectManager $emTsdb): void {
        if (!$rows) {
            return;
        }

        $columns = array_keys($rows[0]);
        $uniqueColumns = self::getLogPrimaryKeyColumns($rows[0]);

        $values = array_map(function (array $row) use ($columns) {
            $singleRow = [];
            foreach ($columns as $column) {
                $singleRow[] = "'" . $row[$column] . "'";
            }
            return '(' . implode(',', $singleRow) . ')';
        }, $rows);

        $columns = array_map(fn($col) => '"' . $col . '"', $columns);

        $sql = sprintf(
            'INSERT INTO %s (%s) VALUES %s ON CONFLICT (%s) DO NOTHING;',
            $tableName,
            implode(',', $columns),
            implode(',', $values),
            implode(',', $uniqueColumns),
        );

        $emTsdb->getConnection()->executeStatement($sql);
    }
}
