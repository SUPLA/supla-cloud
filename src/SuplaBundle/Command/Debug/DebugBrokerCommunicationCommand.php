<?php
namespace SuplaBundle\Command\Debug;

use SuplaBundle\Model\LocalSuplaCloud;
use SuplaBundle\Model\TargetSuplaCloud;
use SuplaBundle\Model\TargetSuplaCloudRequestForwarder;
use SuplaBundle\Supla\SuplaAutodiscover;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class DebugBrokerCommunicationCommand extends Command {

    private SuplaAutodiscover $autodiscover;
    private TargetSuplaCloudRequestForwarder $requestForwarder;
    private LocalSuplaCloud $localSuplaCloud;

    public function __construct(
        SuplaAutodiscover $autodiscover,
        TargetSuplaCloudRequestForwarder $requestForwarder,
        LocalSuplaCloud $localSuplaCloud
    ) {
        parent::__construct();
        $this->autodiscover = $autodiscover;
        $this->requestForwarder = $requestForwarder;
        $this->localSuplaCloud = $localSuplaCloud;
    }

    protected function configure() {
        $this
            ->setName('supla:debug:broker')
            ->setDescription('Debugs broker communication.')
            ->setHidden(true);
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $io = new SymfonyStyle($input, $output);
        $io->title('SUPLA Broker communication debugger');
        $io->section('You are on instance ' . $this->localSuplaCloud->getAddress());
        $io->writeln('AD Enabled? ' . ($this->autodiscover->enabled() ? 'YES' : 'NO'));
        $io->writeln('AD URL: ' . $this->autodiscover->getAutodiscoverUrl());
        $io->writeln('Registered? ' . ($this->autodiscover->isTarget() ? 'YES' : 'NO'));
        $io->writeln('Broker? ' . ($this->autodiscover->isBroker() ? 'YES' : 'NO'));
        if (!$this->autodiscover->isBroker()) {
            $io->error('Sorry. This instance is not a broker.');
            return 1;
        }
        $url = $io->ask('Cloud URL (instance you want to connect to)');
        $target = TargetSuplaCloud::forHost($this->localSuplaCloud->getProtocol(), $url);
        $io->section('Info from ' . $target->getAddress());
        $info = $this->requestForwarder->getInfo($target);
        $io->writeln(json_encode($info, JSON_PRETTY_PRINT));
        return 0;
    }
}
