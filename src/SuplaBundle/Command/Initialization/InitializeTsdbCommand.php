<?php
namespace SuplaBundle\Command\Initialization;

use Doctrine\Persistence\ManagerRegistry;
use SuplaBundle\Utils\DatabaseUtils;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\OutputInterface;

class InitializeTsdbCommand extends Command {
    public function __construct(private ManagerRegistry $doctrineRegistry) {
        parent::__construct();
    }

    protected function configure() {
        $this
            ->setName('supla:initialize:tsdb')
            ->setDescription('Initializes SUPLA Cloud TSDB.');
    }

    /** @inheritdoc */
    protected function execute(InputInterface $input, OutputInterface $output) {
        $this->getApplication()->setAutoExit(false);
        if (DatabaseUtils::getPlatform($this->doctrineRegistry->getManager('measurement_logs')) === DatabaseUtils::PSQL) {
            $this->getApplication()->run(new StringInput("doctrine:migrations:migrate -v --no-interaction --em=measurement_logs --configuration=app/config/migrations_tsdb.yml"), $output);
        }
        return 0;
    }
}
