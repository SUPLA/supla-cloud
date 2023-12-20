<?php
namespace SuplaBundle\Command\User;

use Assert\Assertion;
use SuplaBundle\Entity\EntityUtils;
use SuplaBundle\Entity\Main\User;
use SuplaBundle\EventListener\ApiRateLimit\ApiRateLimitStatus;
use SuplaBundle\EventListener\ApiRateLimit\ApiRateLimitStorage;
use SuplaBundle\EventListener\ApiRateLimit\DefaultUserApiRateLimit;
use SuplaBundle\Model\TimeProvider;
use SuplaBundle\Repository\UserRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

class UserInfoCommand extends Command {
    /** @var UserRepository */
    private $userRepository;
    /** @var DefaultUserApiRateLimit */
    private $defaultUserApiRateLimit;
    /** @var ApiRateLimitStorage */
    private $apiRateLimitStorage;
    /** @var TimeProvider */
    private $timeProvider;

    public function __construct(
        UserRepository $userRepository,
        ApiRateLimitStorage $apiRateLimitStorage,
        DefaultUserApiRateLimit $defaultUserApiRateLimit,
        TimeProvider $timeProvider
    ) {
        parent::__construct();
        $this->userRepository = $userRepository;
        $this->apiRateLimitStorage = $apiRateLimitStorage;
        $this->defaultUserApiRateLimit = $defaultUserApiRateLimit;
        $this->timeProvider = $timeProvider;
    }

    protected function configure() {
        $this
            ->setName('supla:user:info')
            ->setDescription('Displays user info.')
            ->addArgument('username', InputArgument::OPTIONAL, 'Username to display.');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        /** @var QuestionHelper $helper */
        $helper = $this->getHelper('question');
        $email = $input->getArgument('username')
            ?: $helper->ask($input, $output, new Question('Username (email address): '));
        $user = $this->userRepository->findOneByEmail($email);
        Assertion::notNull($user, 'Such user does not exist.');
        $io = new SymfonyStyle($input, $output);
        $io->title($user->getUsername() . " (ID: {$user->getId()})");
        $this->basicInfo($io, $user);
        $this->entityLimitsInfo($io, $user);
        $this->apiRateLimitInfo($io, $user);
        return 0;
    }

    private function basicInfo(SymfonyStyle $io, User $user) {
        $io->writeln('Short Unique ID: ' . $user->getShortUniqueId());
        $io->writeln('Registered: ' . $user->getRegDate()->format(\DateTime::ATOM));
        $io->writeln('Locale: ' . $user->getLocale());
    }

    private function apiRateLimitInfo(SymfonyStyle $io, User $user) {
        $io->section('API Rate Limit');
        $rateLimitRule = $user->getApiRateLimit() ?: $this->defaultUserApiRateLimit;
        $io->writeln('Rule: ' . $rateLimitRule->toString() . ($user->getApiRateLimit() ? '' : ' (default)'));
        $cacheItem = $this->apiRateLimitStorage->getItem($this->apiRateLimitStorage->getUserKey($user));
        if ($cacheItem->isHit()) {
            $status = new ApiRateLimitStatus($cacheItem->get());
        } else {
            $status = ApiRateLimitStatus::fromRule($rateLimitRule, $this->timeProvider);
        }
        $io->writeln('Remaining: ' . $status->getRemaining());
        $io->writeln('Excess: ' . $status->getExcess());
        $io->writeln('Reset: ' . $status->getReset() . ' ' . ((new \DateTime('@' . $status->getReset()))->format(\DateTime::ATOM)));
    }

    private function entityLimitsInfo(SymfonyStyle $io, User $user) {
        $io->section('Entity Limits');
        $io->writeln('Access Identifiers: ' . $user->getAccessIDS()->count() . ' / ' . EntityUtils::getField($user, 'limitAid'));
        $io->writeln('Channel Groups: ' . $user->getChannelGroups()->count() . ' / ' . EntityUtils::getField($user, 'limitChannelGroup'));
        $io->writeln('Channels per Channel Group: ' . EntityUtils::getField($user, 'limitChannelPerGroup'));
        $io->writeln('Direct Links: ' . $user->getDirectLinks()->count() . ' / ' . EntityUtils::getField($user, 'limitDirectLink'));
        $io->writeln('Locations: ' . $user->getLocations()->count() . ' / ' . EntityUtils::getField($user, 'limitLoc'));
        $io->writeln('OAuth Clients: ' . $user->getApiClients()->count() . ' / ' . EntityUtils::getField($user, 'limitOAuthClient'));
        $io->writeln('Schedules: ' . $user->getSchedules()->count() . ' / ' . EntityUtils::getField($user, 'limitSchedule'));
    }
}
