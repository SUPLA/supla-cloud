<?php
namespace SuplaBundle\Command;

use Assert\Assertion;
use Doctrine\ORM\EntityManagerInterface;
use SuplaBundle\Entity\EntityUtils;
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

    public function __construct(UserRepository $userRepository, EntityManagerInterface $entityManager) {
        parent::__construct();
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
    }

    protected function configure() {
        $this
            ->setName('supla:user:change-limits')
            ->setAliases(['supla:change-user-limits'])
            ->setDescription('Allows to change user limits.')
            ->addArgument('username', InputArgument::OPTIONAL, 'Username to update.')
            ->addArgument('limitForAll', InputArgument::OPTIONAL, 'Limit value to set for all limits.');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        /** @var QuestionHelper $helper */
        $helper = $this->getHelper('question');
        $email = $input->getArgument('username')
            ?: $helper->ask($input, $output, new Question('Whose limits do you want to change? (email address): '));
        $user = $this->userRepository->findOneByEmail($email);
        Assertion::notNull($user, 'Such user does not exist.');
        foreach ([
                     'limitAid' => 'Access Identifiers',
                     'limitChannelGroup' => 'Channel Groups',
                     'limitChannelPerGroup' => 'Channels per Channel Group',
                     'limitDirectLink' => 'Direct Links',
                     'limitLoc' => 'Locations',
                     'limitOAuthClient' => 'OAuth Clients',
                     'limitScene' => 'Scenes',
                     'limitSchedule' => 'Schedules',
                 ] as $field => $label) {
            $currentLimit = EntityUtils::getField($user, $field);
            $newLimit = $input->getArgument('limitForAll')
                ?: $helper->ask($input, $output, new Question("Limit of $label [$currentLimit]: ", $currentLimit));
            EntityUtils::setField($user, $field, $newLimit);
        }
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        $output->writeln('<info>User limits have been updated.</info>');
    }
}
