<?php
namespace SuplaBundle\Command;

use Assert\Assertion;
use InvalidArgumentException;
use SuplaBundle\Supla\SuplaAutodiscover;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

class SetAutodiscoverIpsCommand extends Command {
    /** * @var SuplaAutodiscover */
    private $autodiscover;

    public function __construct(SuplaAutodiscover $autodiscover) {
        $this->autodiscover = $autodiscover;
        parent::__construct();
    }

    protected function configure() {
        $this
            ->setName('supla:ad:set-ips')
            ->setDescription('Synchronizes IP adddresses of broker clouds in AD.')
            ->setHidden(true);
    }

    /** @inheritdoc */
    protected function execute(InputInterface $input, OutputInterface $output) {
        Assertion::true($this->autodiscover->isBroker(), 'Command available on broker clouds only.');
        $io = new SymfonyStyle($input, $output);
        $io->comment('Type in all IP addresses of this instance (one by one).' . PHP_EOL . 'Finish by hitting enter on empty input.');
        $hostIp = gethostbyname(gethostname());
        $autocompleteIp = [$hostIp];
        $ips = [];
        do {
            $question = new Question('IP Address');
            $question->setValidator(function ($value) {
                if ($value && !preg_match('#^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$#', $value)) {
                    throw new InvalidArgumentException('Invalid IP address');
                }
                return $value;
            });
            $question->setAutocompleterValues(array_diff($autocompleteIp, $ips));
            $ip = $io->askQuestion($question);
            if ($ip) {
                $ips[] = $ip;
            }
        } while ($ip);
        if (!$ips) {
            $io->error('No IP addresses given.');
            return 2;
        }
        if (count($ips) > 1) {
            $mainIp = $io->choice('Choose the main IP address.', $ips, $ips[0]);
            $ips = array_diff($ips, [$mainIp]);
            $ips = array_values(array_merge([$mainIp], array_values($ips)));
        }
        $io->section('Confirmation');
        $io->text('IP Addresses: ' . implode(', ', $ips));
        $io->text('Main IP Address: ' . $ips[0]);
        if ($io->confirm('Confirm?')) {
            if ($this->autodiscover->setBrokerIpAddresses($ips)) {
                $this->getApplication()->setAutoExit(false);
                $this->getApplication()->run(new StringInput('supla:clean:broker-clouds-cache'), $output);
                $io->success('OK');
            } else {
                $io->error('AD refused to change IP addresses.');
                return 1;
            }
        } else {
            $io->warning('Aborted.');
        }
        return 0;
    }
}
