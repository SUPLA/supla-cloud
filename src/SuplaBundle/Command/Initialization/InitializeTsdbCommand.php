<?php
namespace SuplaBundle\Command\Initialization;

use SuplaBundle\Model\MeasurementLogsEntityManagerProvider;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\OutputInterface;

class InitializeTsdbCommand extends Command {
    public function __construct(private readonly MeasurementLogsEntityManagerProvider $emProvider) {
        parent::__construct();
    }

    protected function configure() {
        $this
            ->setName('supla:initialize:tsdb')
            ->setDescription('Initializes SUPLA Cloud TSDB.')
            ->addOption('force', 'f', InputOption::VALUE_NONE);
    }

    /** @inheritdoc */
    protected function execute(InputInterface $input, OutputInterface $output) {
        $this->getApplication()->setAutoExit(false);
        if ($this->emProvider->isTsdb() || $input->getOption('force')) {
            $migrateCommand =
                "doctrine:migrations:migrate -v --no-interaction --configuration=config/packages/doctrine_migrations_tsdb.yml";
            $this->getApplication()->run(new StringInput($migrateCommand), $output);
            $this->getApplication()->run(new StringInput('supla:initialize:create-tsdb-procedures'), $output);
        }
        return 0;
    }
}
