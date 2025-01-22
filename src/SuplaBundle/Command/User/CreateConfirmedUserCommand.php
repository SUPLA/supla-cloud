<?php
namespace SuplaBundle\Command\User;

use SuplaBundle\Entity\Main\User;
use SuplaBundle\Model\UserManager;
use SuplaBundle\Supla\SuplaAutodiscover;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;

class CreateConfirmedUserCommand extends Command {
    const USERNAME_EXISTS_CODE = 123;
    const MIN_PASSWORD_LENGTH = 4;

    /** @var UserManager */
    private $userManager;
    /** @var SuplaAutodiscover */
    private $autodiscover;

    public function __construct(UserManager $userManager, SuplaAutodiscover $autodiscover) {
        parent::__construct();
        $this->userManager = $userManager;
        $this->autodiscover = $autodiscover;
    }

    protected function configure() {
        $this
            ->setName('supla:user:create')
            ->setAliases(['supla:create-confirmed-user'])
            ->addArgument('username', InputArgument::OPTIONAL)
            ->addArgument('password', InputArgument::OPTIONAL)
            ->addOption('if-not-exists', null, InputOption::VALUE_NONE, 'Don’t throw an exception if the user exists already (for init).')
            ->setDescription('Create a confirmed user account.');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $helper = $this->getHelper('question');
        $username = $input->getArgument('username');
        if (!$username) {
            $username = $helper->ask($input, $output, $this->usernameQuestion());
        } else {
            try {
                $username = $this->validateUsername($username);
            } catch (\RuntimeException $e) {
                if ($e->getCode() == self::USERNAME_EXISTS_CODE && $input->getOption('if-not-exists')) {
                    $output->writeln("User $username already exists.");
                    return;
                } else {
                    throw $e;
                }
            }
        }
        $password = $input->getArgument('password');
        if (!$password) {
            $password = $helper->ask($input, $output, $this->passwordQuestion());
        } else {
            $password = $this->validatePassword($password);
        }
        if ($username && $password) {
            $this->createConfirmedUser($username, $password);
            $output->writeln("New account has been created.");
        }
        return 0;
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
        if ($this->autodiscover->userExists($username)) {
            $targetCloud = $this->autodiscover->getAuthServerForUser($username);
            $onServer = $targetCloud->isLocal() ? '' : " (on {$targetCloud->getHost()})";
            throw new \RuntimeException("User already exists$onServer! Choose different e-mail address.", self::USERNAME_EXISTS_CODE);
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
        if (!is_string($password) || strlen($password) < self::MIN_PASSWORD_LENGTH) {
            throw new \RuntimeException('Password too short (min ' . self::MIN_PASSWORD_LENGTH . ' characters).');
        }
        return $password;
    }

    protected function createConfirmedUser(string $username, string $password) {
        $user = new User();
        $user->setEmail($username);
        if ($this->autodiscover->enabled()) {
            $createdInAd = $this->autodiscover->registerUser($user);
            if (!$createdInAd) {
                $message = "Error when communicating the AD. Try again in a while."; // i18n
                throw new ServiceUnavailableHttpException(10, $message);
            }
        }
        $user->setPlainPassword($password);
        $this->userManager->create($user);
        $this->userManager->setPassword($password, $user, true);
        $this->userManager->confirm($user->getToken());
    }
}
