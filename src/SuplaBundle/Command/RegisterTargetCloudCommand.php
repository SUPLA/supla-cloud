<?php
namespace SuplaBundle\Command;

use Assert\Assertion;
use SuplaBundle\Enums\InstanceSettings;
use SuplaBundle\Model\LocalSuplaCloud;
use SuplaBundle\Repository\SettingsStringRepository;
use SuplaBundle\Supla\SuplaAutodiscover;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RegisterTargetCloudCommand extends Command {
    /** @var SuplaAutodiscover */
    private $autodiscover;
    /** @var LocalSuplaCloud */
    private $localSuplaCloud;
    /** @var SettingsStringRepository */
    private $settingsStringRepository;

    public function __construct(
        SuplaAutodiscover $autodiscover,
        LocalSuplaCloud $localSuplaCloud,
        SettingsStringRepository $settingsStringRepository
    ) {
        parent::__construct();
        $this->autodiscover = $autodiscover;
        $this->localSuplaCloud = $localSuplaCloud;
        $this->settingsStringRepository = $settingsStringRepository;
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
            return 0;
        } catch (\InvalidArgumentException $e) {
            $output->writeln("<error>{$e->getMessage()}</error>");
            return 1;
        }
    }

    private function registerTargetCloud(InputInterface $input, OutputInterface $output) {
        Assertion::false(
            $this->autodiscover->isTarget(),
            'Your instance seems to be registered already. If you have any problems integrating some of the apps, feel free to contact us.'
        );
        $output->writeln('Registering your private SUPLA Cloud...');
        $registrationToken = $input->getArgument('registrationToken');
        $authorizationToken = $this->autodiscover->registerTargetCloud($registrationToken);
        $this->settingsStringRepository->setValue(InstanceSettings::TARGET_TOKEN, $authorizationToken);
        $output->writeln('<info>You have correctly registered your private SUPLA Cloud!');
        $output->writeln("<info>Now, go to {$this->localSuplaCloud->getAddress()}/apps to explore new possibilities!");
    }
}
