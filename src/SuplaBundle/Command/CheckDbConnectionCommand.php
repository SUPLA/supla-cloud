<?php
namespace SuplaBundle\Command;

use Doctrine\Bundle\DoctrineBundle\Command\DoctrineCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;

class CheckDbConnectionCommand extends DoctrineCommand {
    protected function configure() {
        $this
            ->setName('supla:check-db-connection')
            ->setDescription('Checks if the connection with database works.');
    }

    /** @inheritdoc */
    protected function execute(InputInterface $input, OutputInterface $output) {
        $this->getApplication()->setAutoExit(false);
        $this->getApplication()->setCatchExceptions(true);
        $dbConnectionRetries = 10;
        $createDbCommand = new StringInput('doctrine:database:create --if-not-exists --no-interaction');
        do {
            $connectionStatus = $this->getApplication()->run($createDbCommand, $dbConnectionRetries ? new NullOutput() : $output);
            if ($connectionStatus !== 0) {
                if ($dbConnectionRetries <= 0) {
                    throw new \RuntimeException('Could not connect to to the database.');
                } else {
                    $output->writeln("Waiting for database connection ($dbConnectionRetries)...");
                    --$dbConnectionRetries;
                    sleep(3);
                }
            }
        } while ($connectionStatus !== 0);
        $output->writeln("Database connection has been established.");
    }
}
