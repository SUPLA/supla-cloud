<?php
namespace SuplaBundle\Command\Initialization;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Model\Dependencies\ChannelDependencies;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MoveDependentChannelsToTheSameLocationCommand extends Command implements InitializationCommand {
    private const PARENT_FUNCTIONS = [
        ChannelFunction::CONTROLLINGTHEGATEWAYLOCK,
        ChannelFunction::CONTROLLINGTHEGATE,
        ChannelFunction::CONTROLLINGTHEGARAGEDOOR,
        ChannelFunction::CONTROLLINGTHEDOORLOCK,
        ChannelFunction::CONTROLLINGTHEROLLERSHUTTER,
        ChannelFunction::CONTROLLINGTHEROOFWINDOW,
        ChannelFunction::POWERSWITCH,
        ChannelFunction::LIGHTSWITCH,
        ChannelFunction::STAIRCASETIMER,
        ChannelFunction::HVAC_THERMOSTAT,
        ChannelFunction::HVAC_THERMOSTAT_HEAT_COOL,
        ChannelFunction::HVAC_THERMOSTAT_DIFFERENTIAL,
        ChannelFunction::HVAC_DOMESTIC_HOT_WATER,
    ];

    private EntityManagerInterface $em;
    private ChannelDependencies $channelDependencies;
    private LoggerInterface $logger;

    public function __construct(EntityManagerInterface $em, ChannelDependencies $channelDependencies, LoggerInterface $logger) {
        parent::__construct();
        $this->em = $em;
        $this->channelDependencies = $channelDependencies;
        $this->logger = $logger;
    }

    protected function configure() {
        $this
            ->setName('supla:initialize:move-dependent-channels-to-the-same-location')
            ->setDescription('Moves channels that depend on each other to the same location')
            ->setHidden(true);
    }

    /** @inheritdoc */
    protected function execute(InputInterface $input, OutputInterface $output) {
        $channelRepository = $this->em->getRepository(IODeviceChannel::class);
        $channels = $channelRepository->createQueryBuilder('c')
            ->where('c.function IN(:functions)')
            ->setParameter('functions', self::PARENT_FUNCTIONS)
            ->getQuery()
            ->toIterable();
        $changeLocationOperations = [];
        foreach ($channels as $channel) {
            if ($output->isVeryVerbose()) {
                $output->write("Checking dependencies for channel #{$channel->getId()}:");
            }
            $dependencies = $this->channelDependencies->getItemsThatDependOnLocation($channel);
            if ($dependencies['channels']) {
                if ($output->isVeryVerbose()) {
                    $ids = array_map(fn(IODeviceChannel $ch) => $ch->getId(), $dependencies['channels']);
                    $output->writeln(' ' . implode(', ', $ids));
                }
                $expectedLocationId = $channel->getLocation()->getId();
                foreach ($dependencies['channels'] as $depChannel) {
                    /** @var IODeviceChannel $depChannel */
                    if ($depChannel->getLocation()->getId() !== $expectedLocationId) {
                        $changeLocationOperations[$depChannel->getId()] = $this->changeLocationOperation($depChannel, $expectedLocationId);
                    }
                }
            } elseif ($output->isVeryVerbose()) {
                $output->writeln(' none');
            }
        }
        // make sure that everything is migrated
        $channels = $channelRepository->createQueryBuilder('c')
            ->where('c.function NOT IN(:functions)')
            ->setParameter('functions', self::PARENT_FUNCTIONS)
            ->getQuery()
            ->toIterable();
        foreach ($channels as $channel) {
            if ($output->isVeryVerbose()) {
                $output->write("Checking dependencies for channel #{$channel->getId()}:");
            }
            $dependencies = $this->channelDependencies->getItemsThatDependOnLocation($channel);
            if ($dependencies['channels']) {
                if ($output->isVeryVerbose()) {
                    $ids = array_map(fn(IODeviceChannel $ch) => $ch->getId(), $dependencies['channels']);
                    $output->writeln(' ' . implode(', ', $ids));
                }
                $expectedLocationId = $channel->getLocation()->getId();
                if (isset($changeLocationOperations[$channel->getId()])) {
                    $expectedLocationId = $changeLocationOperations[$channel->getId()]['newId'];
                }
                foreach ($dependencies['channels'] as $depChannel) {
                    /** @var IODeviceChannel $depChannel */
                    if (in_array($depChannel->getFunction()->getId(), self::PARENT_FUNCTIONS)) {
                        continue;
                    }
                    if ($depChannel->getLocation()->getId() !== $expectedLocationId) {
                        $changeLocationOperation = $this->changeLocationOperation($depChannel, $expectedLocationId);
                        $changeLocationOperation['WARNING'] = 'No parent function chosen for this relation.';
                        $changeLocationOperations[$depChannel->getId()] = $changeLocationOperation;
                    }
                }
            } elseif ($output->isVeryVerbose()) {
                $output->writeln(' none');
            }
        }
        foreach ($changeLocationOperations as $channelId => $changeOperation) {
            $log = sprintf(
                'Moved channel ID=%d from location ID=%d to ID=%d.',
                $channelId,
                $changeOperation['oldId'],
                $changeOperation['newId']
            );
            $output->writeln($log);
            $this->logger->debug($log, $changeOperation);
            $this->em->createQuery(sprintf('UPDATE %s c SET c.location=:locationId WHERE c.id=:id', IODeviceChannel::class))
                ->execute([
                    'id' => $channelId,
                    'locationId' => $changeOperation['newId'],
                ]);
        }
        return 0;
    }

    private function changeLocationOperation(IODeviceChannel $channel, int $newLocationId): array {
        return [
            'channelId' => $channel->getId(),
            'oldId' => $channel->getLocation()->getId(),
            'newId' => $newLocationId,
            'functionId' => $channel->getFunction()->getId(),
            'functionName' => $channel->getFunction()->getName(),
            'revertSql' => "UPDATE supla_dev_channel SET location_id={$channel->getLocation()->getId()} WHERE id={$channel->getId()};",
        ];
    }
}
