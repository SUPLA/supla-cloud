<?php
namespace SuplaBundle\Command;

use Assert\Assertion;
use Doctrine\ORM\EntityManagerInterface;
use SuplaBundle\Model\UserManager;
use SuplaBundle\Supla\SuplaAutodiscover;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use SuplaBundle\Supla\SuplaServerAware;

class DeleteUserCommand extends ContainerAwareCommand {
    /** @var UserManager */
    private $userManager;
    /** @var EntityManagerInterface */
    private $entityManager;
    /** @var SuplaAutodiscover */
    private $autodiscover;

    use SuplaServerAware;

    public function __construct(UserManager $userManager, EntityManagerInterface $entityManager, SuplaAutodiscover $autodiscover) {
        parent::__construct();
        $this->userManager = $userManager;
        $this->entityManager = $entityManager;
        $this->autodiscover = $autodiscover;
    }

    protected function configure() {
        $this
            ->setName('supla:delete-user')
            ->addArgument('username', InputArgument::OPTIONAL)
            ->setDescription('Deletes user account and all his dependencies from database.');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $helper = $this->getHelper('question');
        $username = $input->getArgument('username');
        if (!$username) {
            $username = $helper->ask($input, $output, new Question('E-mail address: '));
        }
        $user = $this->userManager->userByEmail($username);
        if ($user) {
            $info = <<<USERINFO
You are about to delete <error>{$user->getUsername()}</error>
Registered: {$user->getRegDate()->format(\DateTime::ATOM)}
IO Devices quantity: {$user->getIODevices()->count()}
Channels quantity: {$user->getChannels()->count()}
Are you absolutely sure you want to delete this account along with its data? [y/N]
USERINFO;
            if ($helper->ask($input, $output, new ConfirmationQuestion($info . ' ', !$input->isInteractive()))) {
                $this->entityManager->transactional(function (EntityManagerInterface $em) use ($user) {
                    $deletedFromAd = $this->autodiscover->deleteUser($user);
                    Assertion::true($deletedFromAd, "Could not delete user {$user->getUsername()} in Autodiscover.");
                    $remove = function ($key, $entity) use ($em) {
                        $em->remove($entity);
                        return true;
                    };
                    $user->getAccessIDS()->forAll($remove);
                    $user->getClientApps()->forAll($remove);
                    $user->getChannelGroups()->forAll($remove);
                    $user->getChannels()->forAll($remove);
                    $user->getDirectLinks()->forAll($remove);
                    $user->getIODevices()->forAll($remove);
                    $user->getLocations()->forAll($remove);
                    $user->getSchedules()->forAll($remove);
                    $user->getUserIcons()->forAll($remove);
                    $em->remove($user);
                });

                $this->suplaServer->reconnect($user->getId());

                $output->writeln("<info>User {$user->getUsername()} has been deleted along with his data.</info>");
            } else {
                $output->writeln('Delete operation cancelled, no changes made.');
            }
        } else {
            $output->writeln('<error>User does not exist.</error>');
            return 1;
        }
    }
}
