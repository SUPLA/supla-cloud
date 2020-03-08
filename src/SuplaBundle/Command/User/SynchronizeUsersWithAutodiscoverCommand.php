<?php
namespace SuplaBundle\Command\User;

use Doctrine\ORM\EntityManagerInterface;
use SuplaBundle\Entity\User;
use SuplaBundle\Model\LocalSuplaCloud;
use SuplaBundle\Supla\SuplaAutodiscover;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class SynchronizeUsersWithAutodiscoverCommand extends ContainerAwareCommand {
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
            if ($userServerFromAd !== $this->localSuplaCloud->getHost()) {
                $progressbar->clear();
                $io->section($user->getEmail());
                $io->writeln("Active: " . ($user->isEnabled() ? 'yes' : 'no'));
                $io->writeln("Number of devices: " . $user->getIODevices()->count());
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
                    if ($io->confirm("Delete this user from here ({$this->localSuplaCloud->getHost()})?")) {
                        $command = new StringInput('supla:user:delete ' . $user->getEmail() . ' --no-interaction');
                        $this->getApplication()->run($command, $output);
                    }
                }
                $progressbar->display();
            }
            $progressbar->advance();
        }
        $progressbar->clear();
        $io->newLine();
    }
}
