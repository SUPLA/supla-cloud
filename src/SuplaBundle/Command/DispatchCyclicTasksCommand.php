<?php
namespace SuplaBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\OutputInterface;

class DispatchCyclicTasksCommand extends Command {
    protected function configure() {
        $this
            ->setName('supla:dispatch-cyclic-tasks')
            ->setDescription('Dispatches cyclic tasks.');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $this->getApplication()->setAutoExit(false);
        $minute = intval(date('H')) * 60 + intval(date('i'));
        if ($minute % 3600) { // every six hours
            $this->getApplication()->run(new StringInput('supla:generate-schedules-executions'), $output);
        }
    }
}
