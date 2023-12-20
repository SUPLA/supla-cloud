<?php
namespace SuplaBundle\Command\User;

use Doctrine\ORM\EntityManagerInterface;
use SuplaBundle\Entity\Main\User;
use SuplaBundle\EventListener\ApiRateLimit\ApiRateLimitStatus;
use SuplaBundle\EventListener\ApiRateLimit\ApiRateLimitStorage;
use SuplaBundle\EventListener\ApiRateLimit\DefaultUserApiRateLimit;
use SuplaBundle\EventListener\ApiRateLimit\GlobalApiRateLimit;
use SuplaBundle\Model\TimeProvider;
use SuplaBundle\Repository\UserRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ApiInfoCommand extends Command {
    /** @var UserRepository */
    private $userRepository;
    /** @var DefaultUserApiRateLimit */
    private $defaultUserApiRateLimit;
    /** @var ApiRateLimitStorage */
    private $apiRateLimitStorage;
    /** @var TimeProvider */
    private $timeProvider;
    /** @var GlobalApiRateLimit */
    private $globalApiRateLimit;
    /** @var EntityManagerInterface */
    private $entityManager;
    /** @var bool */
    private $enabled;
    /** @var bool */
    private $blocking;

    public function __construct(
        UserRepository $userRepository,
        ApiRateLimitStorage $apiRateLimitStorage,
        EntityManagerInterface $entityManager,
        GlobalApiRateLimit $globalApiRateLimit,
        DefaultUserApiRateLimit $defaultUserApiRateLimit,
        TimeProvider $timeProvider,
        bool $enabled,
        bool $blocking
    ) {
        parent::__construct();
        $this->userRepository = $userRepository;
        $this->apiRateLimitStorage = $apiRateLimitStorage;
        $this->defaultUserApiRateLimit = $defaultUserApiRateLimit;
        $this->timeProvider = $timeProvider;
        $this->globalApiRateLimit = $globalApiRateLimit;
        $this->entityManager = $entityManager;
        $this->enabled = $enabled;
        $this->blocking = $blocking;
    }

    protected function configure() {
        $this
            ->setName('supla:api:info')
            ->setDescription('Displays API info.')
            ->addOption('users', 'u', InputOption::VALUE_NONE)
            ->addOption('test', 't', InputOption::VALUE_NONE);
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $io = new SymfonyStyle($input, $output);
        $io->title('API Rate Limit');
        $io->writeln('Enabled: ' . ($this->enabled ? 'YES' : 'NO'));
        if ($this->enabled) {
            $io->writeln('Blocking: ' . ($this->blocking ? 'YES' : 'NO'));
            $this->displayGlobalLimitStatus($io);
            if ($input->getOption('users')) {
                $this->displayUsersLimitStatus($io);
            }
            if ($input->getOption('test')) {
                $this->testLimit($io);
            }
        }
        return 0;
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
        $someDisplayed = false;
        foreach ($users as $userRow) {
            /** @var \SuplaBundle\Entity\Main\User $user */
            $user = $userRow[0];
            $cacheItem = $this->apiRateLimitStorage->getItem($this->apiRateLimitStorage->getUserKey($user));
            if ($cacheItem->isHit()) {
                $status = new ApiRateLimitStatus($cacheItem->get());
                if ($status->getRemaining() != $status->getLimit() && $this->timeProvider->getTimestamp() < $status->getReset()) {
                    $info = $this->statusInfo($status);
                    $io->writeln($user->getUsername() . ': ' . $info);
                    $someDisplayed = true;
                }
            }
        }
        if (!$someDisplayed) {
            $io->note('No users use API currently.');
        }
    }

    private function statusInfo(ApiRateLimitStatus $status): string {
        $info = $status->getRemaining() . ' / ' . $status->getLimit();
        if ($status->isExceeded()) {
            $info .= ' (excess: ' . $status->getExcess() . ')';
        }
        $info .= ' (reset: ' . $this->resetInfo($status) . ')';
        return $info;
    }

    private function resetInfo(ApiRateLimitStatus $globalRateLimitStatus) {
        return $globalRateLimitStatus->getReset()
            . ' ('
            . ((new \DateTime('@' . $globalRateLimitStatus->getReset()))->format(\DateTime::ATOM))
            . ')';
    }

    private function testLimit(SymfonyStyle $io) {
        $io->section('API Rate Limit test for hypothetical user (ID: -1)');
        $rateLimitStatus = $this->getTestUserLimitStatus();
        $io->writeln('Limit after got from storage: ' . $this->statusInfo($rateLimitStatus));
        $rateLimitStatus->decrement();
        $io->writeln('Limit after request:          ' . $this->statusInfo($rateLimitStatus));
        $item = $this->apiRateLimitStorage->getItem($this->apiRateLimitStorage->getUserKey('-1'));
        $item->set($rateLimitStatus->toString());
        $item->expiresAfter(1209600); // if not checked in 14 days, expire
        $this->apiRateLimitStorage->save($item);
        $io->write('Waiting 2s...');
        sleep(2);
        $io->write("\r");
        $rateLimitStatus = $this->getTestUserLimitStatus();
        $io->writeln('Limit after got from storage: ' . $this->statusInfo($rateLimitStatus));
    }

    private function getTestUserLimitStatus(): ApiRateLimitStatus {
        $item = $this->apiRateLimitStorage->getItem($this->apiRateLimitStorage->getUserKey('-1'));
        $rateLimitStatus = null;
        if ($item->isHit()) {
            $rateLimitStatus = new ApiRateLimitStatus($item->get());
            if ($rateLimitStatus->isExpired($this->timeProvider)) {
                $rateLimitStatus = null;
            }
        }
        if (!$rateLimitStatus) {
            $rateLimitStatus = ApiRateLimitStatus::fromRule($this->defaultUserApiRateLimit, $this->timeProvider);
        }
        return $rateLimitStatus;
    }
}
