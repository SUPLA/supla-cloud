<?php
namespace SuplaBundle\Command\Cyclic;

use SuplaBundle\Model\TimeProvider;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\OutputInterface;

class DispatchCyclicTasksCommand extends Command {
    /** @var CyclicCommand[]|iterable */
    private $cyclicCommands;
    /** @var TimeProvider */
    private $timeProvider;

    public function __construct(iterable $cyclicCommands, TimeProvider $timeProvider) {
        parent::__construct();
        $this->cyclicCommands = $cyclicCommands;
        $this->timeProvider = $timeProvider;
    }

    protected function configure() {
        $this
            ->setName('supla:dispatch-cyclic-tasks')
            ->setDescription('Dispatches cyclic tasks. Should be executed every minute.');
    }

    /** @inheritdoc */
    protected function execute(InputInterface $input, OutputInterface $output) {
        $this->getApplication()->setAutoExit(false);
        foreach ($this->cyclicCommands as $command) {
            if ($command->shouldRunNow($this->timeProvider)) {
                $output->writeln($command->getName() . ': RUN:');
                $this->getApplication()->run(new StringInput($command->getName()), $output);
            } else {
                $output->writeln($command->getName() . ': NOT NOW');
            }
        }
    }
}
