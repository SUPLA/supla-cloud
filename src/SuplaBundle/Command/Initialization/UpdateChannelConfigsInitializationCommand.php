<?php
namespace SuplaBundle\Command\Initialization;

use Doctrine\ORM\EntityManagerInterface;
use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Model\UserConfigTranslator\SubjectConfigTranslator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateChannelConfigsInitializationCommand extends Command implements InitializationCommand {
    private const BATCH_SIZE = 20;

    /** @var EntityManagerInterface */
    private $entityManager;
    /** @var SubjectConfigTranslator */
    private $paramConfigTranslator;

    public function __construct(EntityManagerInterface $entityManager, SubjectConfigTranslator $paramConfigTranslator) {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->paramConfigTranslator = $paramConfigTranslator;
    }

    protected function configure() {
        $this
            ->setName('supla:initialize:update-channel-configs')
            ->setDescription('Updates channel configuration columns so it matches the values stored in params.');
    }

    /** @inheritdoc */
    protected function execute(InputInterface $input, OutputInterface $output) {
        $count = $this->entityManager
            ->createQuery('SELECT COUNT(c) FROM ' . IODeviceChannel::class . ' c WHERE c.userConfig IS NULL')
            ->getSingleScalarResult();
        $count = intval($count);
        if ($count) {
            $iterator = $this->entityManager
                ->createQuery('SELECT c FROM ' . IODeviceChannel::class . ' c WHERE c.userConfig IS NULL')
                ->iterate();
            $progress = new ProgressBar($output, $count);
            $progress->display();
            $i = 0;
            foreach ($iterator as $row) {
                $channel = $row[0];
                /** @var \SuplaBundle\Entity\Main\IODeviceChannel $channel */
                $config = $this->paramConfigTranslator->getConfig($channel);
                $channel->setUserConfig($config);
                $this->entityManager->persist($channel);
                if ((++$i % self::BATCH_SIZE) === 0) {
                    $this->entityManager->flush();
                    $this->entityManager->clear();
                }
                $progress->advance();
            }
            $this->entityManager->flush();
            $progress->finish();
        } elseif ($input->isInteractive()) {
            $output->writeln('No channels without config found. Nothing changed.');
        }
        return 0;
    }
}
