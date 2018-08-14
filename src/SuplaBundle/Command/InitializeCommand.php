<?php
namespace SuplaBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\OutputInterface;

class InitializeCommand extends Command {
    protected function configure() {
        $this
            ->setName('supla:initialize')
            ->setDescription('Initializes SUPLA Cloud.');
    }

    /** @inheritdoc */
    protected function execute(InputInterface $input, OutputInterface $output) {
        $this->getApplication()->setAutoExit(false);
        $this->getApplication()->run(new StringInput('supla:check-db-connection'), $output);
        $this->getApplication()->setCatchExceptions(false);
        $this->getApplication()->run(new StringInput('doctrine:migrations:migrate --no-interaction --allow-no-migration'), $output);
        $this->getApplication()->run(new StringInput('supla:oauth:create-webapp-client'), $output);
    }
}
