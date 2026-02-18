<?php
namespace SuplaBundle\Command\Autodiscover;

use SuplaBundle\Command\Initialization\InitializationCommand;
use SuplaBundle\Enums\InstanceSettings;
use SuplaBundle\Repository\SettingsStringRepository;
use SuplaBundle\Supla\SuplaAutodiscover;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class AutodiscoverFetchSettingsCommand extends Command implements InitializationCommand {
    public function __construct(private SuplaAutodiscover $autodiscover, private SettingsStringRepository $settingsRepo) {
        parent::__construct();
    }

    protected function configure(): void {
        $this
            ->setName('supla:ad:fetch-settings')
            ->setDescription('Fetches settings for this instance from SUPLA Autodiscover.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int {
        $io = new SymfonyStyle($input, $output);
        if (!$this->autodiscover->isTarget()) {
            if ($io->isVerbose()) {
                $io->error('Your instance is not registered yet. Please register it first.');
            }
            return 0;
        }
        $info = $this->autodiscover->getInfo();
        if (!($info['isTarget'] ?? false) || !isset($info['settings'])) {
            $io->error('Invalid AD response. Could not fetch settings.');
            $io->comment(json_encode($info, JSON_PRETTY_PRINT));
            return 1;
        }
        $settings = $info['settings'];
        $this->settingsRepo->setValueBoolean(InstanceSettings::ALLOW_TGE_REPORTS, $settings['allowTgeReports'] ?? false);
        $this->settingsRepo->setValueBoolean(InstanceSettings::ALLOW_NOTIFICATIONS, $settings['allowNotifications'] ?? false);
        $this->settingsRepo->setValue(InstanceSettings::AD_NOTIFICATIONS_LIMIT, $settings['limitTotalPushNotifications'] ?? 0);
        if ($io->isVerbose()) {
            $io->writeln('Allow TGE reports: ' . ($this->settingsRepo->getValueBoolean(InstanceSettings::ALLOW_TGE_REPORTS) ? 'YES' : 'NO'));
            $io->writeln('Allow notifications: ' . ($this->settingsRepo->getValueBoolean(InstanceSettings::ALLOW_NOTIFICATIONS) ? 'YES' : 'NO'));
            $io->writeln('Notifications limit: ' . $this->settingsRepo->getValue(InstanceSettings::AD_NOTIFICATIONS_LIMIT));
        }
        return 0;
    }
}
