<?php
namespace SuplaBundle\Command;

use Assert\Assertion;
use SuplaBundle\Supla\SuplaAutodiscover;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RegisterTargetCloudCommand extends Command {
    /** @var SuplaAutodiscover */
    private $autodiscover;

    public function __construct(SuplaAutodiscover $autodiscover) {
        parent::__construct();
        $this->autodiscover = $autodiscover;
    }

    protected function configure() {
        $this
            ->setName('supla:register-target-cloud')
            ->setDescription('Registers this instance to work with OAuth applications from cloud.supla.org')
            ->addArgument('registrationToken', InputArgument::REQUIRED, 'Registration token issued on cloud.supla.org/register-cloud');
    }

    /** @inheritdoc */
    protected function execute(InputInterface $input, OutputInterface $output) {
        try {
            $this->registerTargetCloud($input, $output);
        } catch (\InvalidArgumentException $e) {
            $output->writeln("<error>{$e->getMessage()}</error>");
            return 1;
        }
    }

    private function registerTargetCloud(InputInterface $input, OutputInterface $output) {
        Assertion::false(
            $this->autodiscover->isTarget(),
            'Your isntance seems to be registered already. If you have any problems integrating some of the apps, feel free to contact us.'
        );
        $output->writeln('Registering your private SUPLA Cloud...');
        $registrationToken = $input->getArgument('registrationToken');
        $authorizationToken = $this->autodiscover->registerTargetCloud($registrationToken);
        $written = file_put_contents(SuplaAutodiscover::TARGET_CLOUD_TOKEN_SAVE_PATH, $authorizationToken);
        Assertion::greaterThan($written, 0, 'Could not save the token file. Please contact us with your cloud address name.');
        $chmodChanged = chmod(SuplaAutodiscover::TARGET_CLOUD_TOKEN_SAVE_PATH, 0600);
        Assertion::true($chmodChanged, 'Could not change the token file permissions.');
        $output->writeln('<info>You have correctly registered your private SUPLA Cloud!');
        $localInstance = $this->autodiscover->getAuthServerForUser('target');
        $output->writeln("<info>Now, go to {$localInstance->getAddress()}/apps to explore new possibilities!");
    }
}
