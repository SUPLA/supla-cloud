<?php
namespace SuplaBundle\Command\User;

use Doctrine\ORM\EntityManagerInterface;
use SuplaBundle\Entity\User;
use SuplaBundle\Supla\SuplaAutodiscover;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class SynchronizeUsersWithAutodiscoverCommand extends ContainerAwareCommand {
    /** @var EntityManagerInterface */
    private $entityManager;
    /** @var SuplaAutodiscover */
    private $autodiscover;

    public function __construct(EntityManagerInterface $entityManager, SuplaAutodiscover $autodiscover) {
        parent::__construct();
        $this->autodiscover = $autodiscover;
        $this->entityManager = $entityManager;
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
        $progressbar = new ProgressBar($output, $userCount);
        $progressbar->start();
        foreach ($users as $userRow) {
            usleep(20000);
            /** @var User $user */
            $user = $userRow[0];
            if (!$this->autodiscover->userExistsInAd($user->getEmail())) {
                $progressbar->clear();
                $registered = $this->autodiscover->registerUser($user);
                if ($registered) {
                    $io->writeln('Registered user in AD: ' . $user->getEmail());
                } else {
                    $io->warning('Could not register user in AD: ' . $user->getEmail());
                }
                $progressbar->display();
            }
            $progressbar->advance();
        }
        $progressbar->clear();
        $io->newLine();
    }
}
