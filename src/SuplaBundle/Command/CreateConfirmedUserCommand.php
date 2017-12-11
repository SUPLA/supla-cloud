<?php
namespace SuplaBundle\Command;

use SuplaBundle\Entity\User;
use SuplaBundle\Model\UserManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class CreateConfirmedUserCommand extends ContainerAwareCommand {
    /** @var UserManager */
    private $userManager;

    protected function configure() {
        $this
            ->setName('supla:create-confirmed-user')
            ->addArgument('username', InputArgument::OPTIONAL)
            ->addArgument('password', InputArgument::OPTIONAL)
            ->setDescription('Create a confirmed user account.');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        if (!$this->userManager) {
            $this->userManager = $this->getContainer()->get('user_manager');
        }
        $helper = $this->getHelper('question');
        $username = $input->getArgument('username');
        if (!$username) {
            $username = $helper->ask($input, $output, $this->usernameQuestion());
        } else {
            $username = $this->validateUsername($username);
        }
        $password = $input->getArgument('password');
        if (!$password) {
            $password = $helper->ask($input, $output, $this->passwordQuestion());
        } else {
            $password = $this->validatePassword($password);
        }
        $this->createConfirmedUser($username, $password);
        $output->writeln("New account has been created.");
    }

    private function usernameQuestion(): Question {
        $question = new Question('E-mail address: ');
        $question->setValidator([$this, 'validateUsername']);
        return $question;
    }

    public function validateUsername($username): string {
        if (!is_string($username) || strlen($username) < 4 || !filter_var($username, FILTER_VALIDATE_EMAIL)) {
            throw new \RuntimeException('Invalid e-mail address');
        }
        $username = trim($username);
        if ($this->userManager->userByEmail($username)) {
            throw new \RuntimeException("User already exists! Choose different e-mail address.");
        }
        return $username;
    }

    private function passwordQuestion(): Question {
        $question = new Question('Password: ');
        $question->setHidden(true);
        $question->setHiddenFallback(false);
        $question->setValidator([$this, 'validatePassword']);
        return $question;
    }

    public function validatePassword($password): string {
        if (!is_string($password) || strlen($password) < 8) {
            throw new \RuntimeException('Password too short (min 8 characters).');
        }
        return $password;
    }

    protected function createConfirmedUser(string $username, string $password) {
        $user = new User();
        $user->setEmail($username);
        $this->userManager->create($user);
        $this->userManager->setPassword($password, $user, true);
        $this->userManager->confirm($user->getToken());
    }
}
