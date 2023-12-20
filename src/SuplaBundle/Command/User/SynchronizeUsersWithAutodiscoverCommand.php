<?php
namespace SuplaBundle\Command\User;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use SuplaBundle\Entity\Main\User;
use SuplaBundle\Model\LocalSuplaCloud;
use SuplaBundle\Supla\SuplaAutodiscover;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class SynchronizeUsersWithAutodiscoverCommand extends Command {
    /** @var EntityManagerInterface */
    private $entityManager;
    /** @var SuplaAutodiscover */
    private $autodiscover;
    /** * @var LocalSuplaCloud */
    private $localSuplaCloud;

    public function __construct(EntityManagerInterface $entityManager, SuplaAutodiscover $autodiscover, LocalSuplaCloud $localSuplaCloud) {
        parent::__construct();
        $this->autodiscover = $autodiscover;
        $this->entityManager = $entityManager;
        $this->localSuplaCloud = $localSuplaCloud;
    }

    protected function configure() {
        $this
            ->setName('supla:user:synchronize-with-autodiscover')
            ->setDescription('Registers all users in AD that were not there for whatever reason.')
            ->setHidden(true);
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $io = new SymfonyStyle($input, $output);
        if (!$this->autodiscover->isBroker()) {
            $io->error('This Cloud instance is not a Broker.');
        }
        $userClass = User::class;
        $userCount = $this->entityManager->createQuery("SELECT COUNT(u) FROM $userClass u")->getSingleScalarResult();
        $users = $this->entityManager->createQuery("SELECT u FROM $userClass u")->iterate();
        $this->getApplication()->setAutoExit(false);
        $progressbar = new ProgressBar($output, $userCount);
        $progressbar->start();
        foreach ($users as $userRow) {
            usleep(20000);
            /** @var User $user */
            $user = $userRow[0];
            $userServerFromAd = $this->autodiscover->getUserServerFromAd($user->getEmail());
            if ($userServerFromAd !== $this->localSuplaCloud->getAddress()) {
                $progressbar->clear();
                $io->section($user->getEmail());
                $io->writeln("Active: " . ($user->isEnabled() ? 'yes' : 'no'));
                $io->writeln("Number of devices: " . $user->getIODevices()->count());
                $io->writeln("Created: " . $user->getRegDate()->format(DateTime::ATOM));
                $io->newLine();
                if (!$userServerFromAd) {
                    if ($io->confirm("User not registered in AD. Register?")) {
                        $registered = $this->autodiscover->registerUser($user);
                        if ($registered) {
                            $io->writeln("Registered user in AD for this Target Cloud ({$this->localSuplaCloud->getHost()})");
                        } else {
                            $io->warning('Could not register user in AD: ' . $user->getEmail());
                        }
                    }
                } else {
                    $io->warning("User registered for different Target Cloud ($userServerFromAd).");
                    $deleteNow = !$user->isEnabled() && $user->getIODevices()->isEmpty()
                        && $user->getRegDate()->getTimestamp() < time() - 86400;
                    if ($deleteNow) {
                        $io->writeln("Automatically deleting {$user->getEmail()}");
                    }
                    if ($deleteNow || $io->confirm("Delete this user from here ({$this->localSuplaCloud->getHost()})?")) {
                        $command = new ArrayInput([
                            'command' => 'supla:user:delete',
                            'username' => $user->getEmail(),
                            '--no-interaction' => true,
                        ]);
                        $this->getApplication()->run($command, $output);
                    }
                }
                $progressbar->display();
            }
            $progressbar->advance();
        }
        $progressbar->clear();
        $io->newLine();
        return 0;
    }
}
