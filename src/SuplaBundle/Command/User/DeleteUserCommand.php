<?php
namespace SuplaBundle\Command\User;

use SuplaBundle\Model\UserManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

class DeleteUserCommand extends Command {
    /** @var UserManager */
    private $userManager;

    public function __construct(UserManager $userManager) {
        parent::__construct();
        $this->userManager = $userManager;
    }

    protected function configure() {
        $this
            ->setName('supla:user:delete')
            ->setAliases(['supla:delete-user'])
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
                $this->userManager->deleteAccount($user);
                $output->writeln("<info>User {$user->getUsername()} has been deleted along with his data.</info>");
            } else {
                $output->writeln('Delete operation cancelled, no changes made.');
            }
        } else {
            $output->writeln('<error>User does not exist.</error>');
            return 1;
        }
        return 0;
    }
}
