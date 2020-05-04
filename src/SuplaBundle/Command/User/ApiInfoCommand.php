<?php
namespace SuplaBundle\Command\User;

use Doctrine\ORM\EntityManagerInterface;
use SuplaBundle\Entity\User;
use SuplaBundle\EventListener\ApiRateLimit\ApiRateLimitStatus;
use SuplaBundle\EventListener\ApiRateLimit\ApiRateLimitStorage;
use SuplaBundle\EventListener\ApiRateLimit\DefaultUserApiRateLimit;
use SuplaBundle\EventListener\ApiRateLimit\GlobalApiRateLimit;
use SuplaBundle\Model\TimeProvider;
use SuplaBundle\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ApiInfoCommand extends ContainerAwareCommand {
    /** @var UserRepository */
    private $userRepository;
    /** @var DefaultUserApiRateLimit */
    private $defaultUserApiRateLimit;
    /** @var ApiRateLimitStorage */
    private $apiRateLimitStorage;
    /** @var TimeProvider */
    private $timeProvider;
    /**
     * @var GlobalApiRateLimit
     */
    private $globalApiRateLimit;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(
        UserRepository $userRepository,
        ApiRateLimitStorage $apiRateLimitStorage,
        EntityManagerInterface $entityManager,
        GlobalApiRateLimit $globalApiRateLimit,
        DefaultUserApiRateLimit $defaultUserApiRateLimit,
        TimeProvider $timeProvider
    ) {
        parent::__construct();
        $this->userRepository = $userRepository;
        $this->apiRateLimitStorage = $apiRateLimitStorage;
        $this->defaultUserApiRateLimit = $defaultUserApiRateLimit;
        $this->timeProvider = $timeProvider;
        $this->globalApiRateLimit = $globalApiRateLimit;
        $this->entityManager = $entityManager;
    }

    protected function configure() {
        $this
            ->setName('supla:api:info')
            ->setDescription('Displays API info.')
            ->addOption('users', 'u', InputOption::VALUE_NONE);
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $io = new SymfonyStyle($input, $output);
        $this->displayGlobalLimitStatus($io);
        if ($input->getOption('users')) {
            $this->displayUsersLimitStatus($io);
        } else {
            $io->newLine();
            $io->note('To display users limits, add -u');
        }
    }

    protected function displayGlobalLimitStatus(SymfonyStyle $io) {
        $io->section('Global Limit');
        $item = $this->apiRateLimitStorage->getItem($this->apiRateLimitStorage->getGlobalKey());
        if ($item->isHit()) {
            $globalRateLimitStatus = new ApiRateLimitStatus($item->get());
        } else {
            $globalRateLimitStatus = ApiRateLimitStatus::fromRule($this->globalApiRateLimit, $this->timeProvider);
        }
        $io->writeln('Rule: ' . $this->globalApiRateLimit->toString());
        $io->writeln('Remaining: ' . $globalRateLimitStatus->getRemaining());
        $io->writeln('Excess: ' . $globalRateLimitStatus->getExcess());
        $io->writeln('Reset: ' . $this->resetInfo($globalRateLimitStatus));
    }

    private function displayUsersLimitStatus(SymfonyStyle $io) {
        $io->section('Users Limits');
        $userClass = User::class;
        $users = $this->entityManager->createQuery("SELECT u FROM $userClass u")->iterate();
        foreach ($users as $userRow) {
            /** @var User $user */
            $user = $userRow[0];
            $cacheItem = $this->apiRateLimitStorage->getItem($this->apiRateLimitStorage->getUserKey($user));
            if ($cacheItem->isHit()) {
                $status = new ApiRateLimitStatus($cacheItem->get());
                if ($status->getRemaining() != $status->getLimit() && $this->timeProvider->getTimestamp() < $status->getReset()) {
                    $info = $status->getRemaining() . ' / ' . $status->getLimit();
                    if ($status->isExceeded()) {
                        $info .= ' (excess: ' . $status->getExcess() . ')';
                    }
                    $info .= ' (reset: ' . $this->resetInfo($status) . ')';
                    $io->writeln($user->getUsername() . ': ' . $info);
                }
            }
        }
    }

    private function resetInfo(ApiRateLimitStatus $globalRateLimitStatus) {
        return $globalRateLimitStatus->getReset()
            . ' ('
            . ((new \DateTime('@' . $globalRateLimitStatus->getReset()))->format(\DateTime::ATOM))
            . ')';
    }
}
