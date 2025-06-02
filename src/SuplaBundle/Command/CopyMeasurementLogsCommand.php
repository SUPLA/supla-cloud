<?php
namespace SuplaBundle\Command;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\OutputInterface;

class CopyMeasurementLogsCommand extends Command {
    private const LOG_TABLES = [
        'supla_em_current_log',
        'supla_em_log',
        'supla_em_power_active_log',
        'supla_em_voltage_aberration_log',
        'supla_em_voltage_log',
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
            ->setDescription('Copies measurement logs from MariaDB to TSDB and vice versa.')
            ->setHidden(true);
    }

    /** @inheritdoc */
    protected function execute(InputInterface $input, OutputInterface $output) {
        $emMariadb = $this->registry->getManager('default');
        $emTsdb = $this->registry->getManager('tsdb');
        $this->getApplication()->setAutoExit(false);
        $this->getApplication()->run(new StringInput("doctrine:database:create --connection=tsdb --if-not-exists --no-interaction"), $output);
        $this->getApplication()->run(new StringInput("doctrine:migrations:migrate -v --no-interaction --em=tsdb --configuration=app/config/migrations_tsdb.yml"), $output);

        $table = new Table($output);
        $table->setHeaders(['Log table', 'MariaDB Count', 'TSDB Count']);

        foreach (self::LOG_TABLES as $tableName) {
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

        foreach (self::LOG_TABLES as $tableName) {
            $output->writeln("\nCopying $tableName...");

            $batchSize = 1000;
            $offset = 0;

            while (true) {
                $rows = $emMariadb->getConnection()
                    ->executeQuery("SELECT * FROM $tableName LIMIT $batchSize OFFSET $offset")
                    ->fetchAllAssociative();

                if (empty($rows)) {
                    break;
                }

                $columns = array_keys($rows[0]);
                $uniqueColumns = in_array('phase_no', $columns) ? ['channel_id', 'date', 'phase_no'] : ['channel_id', 'date'];
                $placeholders = '(' . implode(',', array_fill(0, count($columns), '?')) . ')';
                $sql = sprintf(
                    'INSERT INTO %s (%s) VALUES %s ON CONFLICT (%s) DO NOTHING;',
                    $tableName,
                    implode(',', $columns),
                    implode(',', array_fill(0, count($rows), $placeholders)),
                    implode(',', $uniqueColumns),
                );

                $values = [];
                foreach ($rows as $row) {
                    foreach ($row as $value) {
                        $values[] = $value;
                    }
                }

                $emTsdb->getConnection()->executeStatement($sql, $values);

                $offset += $batchSize;
                if ($offset % 10000 === 0) {
                    $output->write('.');
                }
            }

            $output->writeln('Done!');
        }

        return 0;
    }
}
