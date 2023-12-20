<?php
namespace SuplaBundle\Command\User;

use Assert\Assertion;
use Doctrine\ORM\EntityManagerInterface;
use SuplaBundle\Entity\EntityUtils;
use SuplaBundle\Entity\Main\User;
use SuplaBundle\EventListener\ApiRateLimit\ApiRateLimitRule;
use SuplaBundle\EventListener\ApiRateLimit\ApiRateLimitStorage;
use SuplaBundle\EventListener\ApiRateLimit\DefaultUserApiRateLimit;
use SuplaBundle\Repository\UserRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class ChangeUserLimitsCommand extends Command {
    /** @var UserRepository */
    private $userRepository;
    /** @var EntityManagerInterface */
    private $entityManager;
    /** @var DefaultUserApiRateLimit */
    private $defaultUserApiRateLimit;
    /** @var ApiRateLimitStorage */
    private $apiRateLimitStorage;

    public function __construct(
        UserRepository $userRepository,
        EntityManagerInterface $entityManager,
        ApiRateLimitStorage $apiRateLimitStorage,
        DefaultUserApiRateLimit $defaultUserApiRateLimit
    ) {
        parent::__construct();
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
        $this->apiRateLimitStorage = $apiRateLimitStorage;
        $this->defaultUserApiRateLimit = $defaultUserApiRateLimit;
    }

    protected function configure() {
        $this
            ->setName('supla:user:change-limits')
            ->setAliases(['supla:change-user-limits'])
            ->setDescription('Allows to change user limits.')
            ->addArgument('username', InputArgument::OPTIONAL, 'Username to update.')
            ->addArgument('limitForAll', InputArgument::OPTIONAL, 'Limit value to set for all limits. Optional constants: [default, big].');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        /** @var QuestionHelper $helper */
        $helper = $this->getHelper('question');
        $email = $input->getArgument('username')
            ?: $helper->ask($input, $output, new Question('Whose limits do you want to change? (email address): '));
        $user = $this->userRepository->findOneByEmail($email);
        Assertion::notNull($user, 'Such user does not exist.');
        $limitForAll = $input->getArgument('limitForAll');
        $limitForAll = $limitForAll ? $this->getLimits($user, $limitForAll) : null;
        foreach ([
                     'limitIoDev' => 'IO Devices',
                     'limitClientApp' => 'Client Apps (smartphones)',
                     'limitAid' => 'Access Identifiers',
                     'limitChannelGroup' => 'Channel Groups',
                     'limitChannelPerGroup' => 'Channels per Channel Group',
                     'limitDirectLink' => 'Direct Links',
                     'limitLoc' => 'Locations',
                     'limitOAuthClient' => 'OAuth Clients',
                     'limitScene' => 'Scenes',
                     'limitOperationsPerScene' => 'Operations per Scene',
                     'limitSchedule' => 'Schedules',
                     'limitActionsPerSchedule' => 'Actions per Schedule',
                     'limitPushNotifications' => 'Push notifications',
                     'limitPushNotificationsPerHour' => 'Push notifications per hour',
                     'limitValueBasedTriggers' => 'Value based triggers (reactions)',
                 ] as $field => $label) {
            $currentLimit = EntityUtils::getField($user, $field);
            $newLimit = $limitForAll
                ? $limitForAll[$field]
                : $helper->ask($input, $output, new Question("Limit of $label [$currentLimit]: ", $currentLimit));
            EntityUtils::setField($user, $field, $newLimit);
        }
        if (!$limitForAll) {
            $currentRule = $user->getApiRateLimit() ?: $this->defaultUserApiRateLimit;
            $newRule = $helper->ask($input, $output, $this->apiRateLimitQuestion($currentRule));
            if ($newRule != $currentRule) {
                $user->setApiRateLimit($newRule == $this->defaultUserApiRateLimit ? null : $newRule);
                $this->apiRateLimitStorage->clearUserLimit($user);
            }
        }
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        $output->writeln('<info>User limits have been updated.</info>');
        return 0;
    }

    private function apiRateLimitQuestion(ApiRateLimitRule $currentLimit): Question {
        $q = new Question("API Rate limit (req/sec or default) [$currentLimit]: ", $currentLimit);
        $q->setValidator(function ($v) {
            if ($v === 'default') {
                return $this->defaultUserApiRateLimit;
            } else {
                $rule = new ApiRateLimitRule($v);
                Assertion::true($rule->isValid(), 'Invalid API rate limit rule. Format: limit/seconds');
                return $rule;
            }
        });
        return $q;
    }

    private function getLimits(User $user, string $limitForAll): array {
        if (isset(User::PREDEFINED_LIMITS[$limitForAll])) {
            return User::PREDEFINED_LIMITS[$limitForAll];
        }
        if (preg_match('#^([\+\*x])(\d)$#', $limitForAll, $match)) {
            $limits = [];
            foreach (array_keys(User::PREDEFINED_LIMITS['default']) as $field) {
                $currentLimit = EntityUtils::getField($user, $field);
                $newLimit = $match[1] === '+' ? $currentLimit + $match[2] : $currentLimit * $match[2];
                $limits[$field] = $newLimit;
            }
            return $limits;
        }
        if (is_numeric($limitForAll)) {
            Assertion::greaterThan($limitForAll, 0);
            return array_map(function ($value) use ($limitForAll) {
                return $limitForAll;
            }, User::PREDEFINED_LIMITS['default']);
        }
        throw new \InvalidArgumentException('Invalid limit specified.');
    }
}
