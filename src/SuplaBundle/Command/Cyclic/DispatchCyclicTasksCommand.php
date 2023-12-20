<?php
namespace SuplaBundle\Command\Cyclic;

use SuplaBundle\Model\TimeProvider;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class DispatchCyclicTasksCommand extends Command {
    /** @var CyclicCommand[]|iterable */
    private $cyclicCommands;
    /** @var TimeProvider */
    private $timeProvider;

    public function __construct($cyclicCommands, TimeProvider $timeProvider) {
        parent::__construct();
        $this->cyclicCommands = $cyclicCommands;
        $this->timeProvider = $timeProvider;
    }

    protected function configure() {
        $this
            ->setName('supla:dispatch-cyclic-tasks')
            ->setDescription('Dispatches cyclic tasks. Should be executed every minute.')
            ->addOption('all', 'a', InputOption::VALUE_NONE, 'Run all cyclic tasks now, regardless of their interval settings.');
    }

    /** @inheritdoc */
    protected function execute(InputInterface $input, OutputInterface $output) {
        $io = new SymfonyStyle($input, $output);
        $this->getApplication()->setAutoExit(false);
        $runAll = $input->getOption('all');
        $notRunCommands = [];
        foreach ($this->cyclicCommands as $command) {
            if ($runAll || $command->shouldRunNow($this->timeProvider)) {
                if (!$output->isQuiet()) {
                    $io->section($command->getName());
                }
                $this->getApplication()->run(new StringInput($command->getName()), $output);
                if (!$output->isQuiet()) {
                    $io->newLine();
                }
            } else {
                $notRunCommands[] = $command->getName();
            }
        }
        if ($notRunCommands && $output->isVerbose()) {
            $io->section('These commands were not meant to run now');
            $io->writeln(implode(PHP_EOL, $notRunCommands));
            $io->note('Add -a to run these commands anyway.');
        }
        return 0;
    }
}
