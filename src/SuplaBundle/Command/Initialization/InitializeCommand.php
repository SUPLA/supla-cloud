<?php
namespace SuplaBundle\Command\Initialization;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\OutputInterface;

class InitializeCommand extends Command {
    /** @var InitializationCommand[] */
    private $initializationCommands;

    public function __construct(iterable $initializationCommands) {
        parent::__construct();
        $this->initializationCommands = $initializationCommands;
    }

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
        $this->getApplication()->run(new StringInput('doctrine:migrations:sync-metadata-storage --no-interaction'), $output);
        $this->getApplication()->run(new StringInput('doctrine:migrations:migrate --no-interaction --allow-no-migration'), $output);
        $this->getApplication()->run(new StringInput('messenger:setup-transports'), $output);
        foreach ($this->initializationCommands as $initializationCommand) {
            $this->getApplication()->run(new StringInput($initializationCommand->getName() . ' --no-interaction'), $output);
        }
        return 0;
    }
}
