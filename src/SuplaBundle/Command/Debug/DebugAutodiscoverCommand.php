<?php
namespace SuplaBundle\Command\Debug;

use SuplaBundle\Supla\SuplaAutodiscover;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class DebugAutodiscoverCommand extends Command {

    private SuplaAutodiscover $autodiscover;

    public function __construct(SuplaAutodiscover $autodiscover) {
        parent::__construct();
        $this->autodiscover = $autodiscover;
    }

    protected function configure() {
        $this
            ->setName('supla:debug:ad')
            ->setDescription('Debugs AD communication.')
            ->setHidden(true);
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $io = new SymfonyStyle($input, $output);
        $io->title('SUPLA Autodiscover debugger');
        $io->writeln('Enabled? ' . ($this->autodiscover->enabled() ? 'YES' : 'NO'));
        $io->writeln('URL: ' . $this->autodiscover->getAutodiscoverUrl());
        $io->writeln('Registered? ' . ($this->autodiscover->isTarget() ? 'YES' : 'NO'));
        $io->writeln('Broker? ' . ($this->autodiscover->isBroker() ? 'YES' : 'NO'));
        do {
            $choice = $io->choice('What do you want to debug?', [
                'exit',
                'getInfo',
                'getBrokerClouds',
                'getAuthServerForUser',
            ], 0);
            $this->debugChoice($io, $choice);
        } while ($choice !== 'exit');
        return 0;
    }

    private function debugChoice(SymfonyStyle $io, string $choice) {
        switch ($choice) {
            case 'getInfo':
                return $this->debugGetInfo($io);
            case 'getBrokerClouds':
                return $this->debugGetBrokerClouds($io);
            case 'getAuthServerForUser':
                return $this->debugGetAuthServerForUser($io);
        }
    }

    private function debugGetInfo(SymfonyStyle $io) {
        $info = $this->autodiscover->getInfo();
        $io->comment(json_encode($info, JSON_PRETTY_PRINT));
    }

    private function debugGetBrokerClouds(SymfonyStyle $io) {
        $clouds = $this->autodiscover->getBrokerClouds();
        $cloudsTable = array_map(fn(array $arr) => array_intersect_key($arr, array_flip(['id', 'url', 'ip'])), $clouds);
        $io->table(array_keys($cloudsTable[0]), $cloudsTable);
        if ($io->isVerbose()) {
            $io->comment(json_encode($clouds, JSON_PRETTY_PRINT));
        }
        $io->writeln('Cache exists? ' . (file_exists(SuplaAutodiscover::BROKER_CLOUDS_SAVE_PATH) ? 'YES' : 'NO'));
    }

    private function debugGetAuthServerForUser(SymfonyStyle $io) {
        $username = $io->ask('Username');
        $server = $this->autodiscover->getAuthServerForUser($username);
        $io->writeln('Local? ' . ($server->isLocal() ? 'YES' : 'NO'));
        $io->writeln('Address: ' . $server->getAddress());
    }
}
