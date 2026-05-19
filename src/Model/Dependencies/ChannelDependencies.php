<?php

namespace App\Model\Dependencies;

use App\Entity\Main\IODevice;
use App\Entity\Main\IODeviceChannel;
use App\Enums\ChannelFunction;
use App\Enums\ChannelType;
use App\Model\Schedule\ScheduleManager;
use App\Model\UserConfigTranslator\SubjectConfigTranslator;
use App\Repository\IODeviceChannelRepository;
use App\Utils\ArrayUtils;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * This class is responsible for detecting and possibly clearing all items that depend on the given channel (and its function).
 */
class ChannelDependencies extends ActionableSubjectDependencies {
    /** @var ScheduleManager */
    private $scheduleManager;
    /** @var ChannelGroupDependencies */
    private $channelGroupDependencies;
    /** @var IODeviceChannelRepository */
    private $channelRepository;
    /** @var LoggerInterface */
    private $logger;

    private array $dependencyCache = [];
    private array $deviceDependencyIndexCache = [];
    private array $actionTriggersCache = [];

    public function __construct(
        EntityManagerInterface $entityManager,
        SubjectConfigTranslator $channelParamConfigTranslator,
        ScheduleManager $scheduleManager,
        ChannelGroupDependencies $channelGroupDependencies,
        IODeviceChannelRepository $channelRepository,
        LoggerInterface $logger
    ) {
        parent::__construct($entityManager, $channelParamConfigTranslator);
        $this->scheduleManager = $scheduleManager;
        $this->channelGroupDependencies = $channelGroupDependencies;
        $this->channelRepository = $channelRepository;
        $this->logger = $logger;
    }

    public function getItemsThatDependOnFunction(IODeviceChannel $channel): array {
        return [
            'channels' => array_values($this->findDependentChannels($channel)),
            'channelGroups' => $channel->getChannelGroups()->toArray(),
            'directLinks' => $channel->getDirectLinks()->toArray(),
            'schedules' => $channel->getSchedules()->toArray(),
            'sceneOperations' => $channel->getSceneOperations()->toArray(),
            'actionTriggers' => $this->findActionTriggersForSubject($channel)->getValues(),
            'ownReactions' => $channel->getOwnReactions()->toArray(),
            'reactions' => $channel->getReactions()->toArray(),
        ];
    }

    public function getItemsThatDependOnLocation(IODeviceChannel $channel): array {
        $dependentChannels = $this->findDependentChannelsRecursive($channel, [
            'auxThermometerChannelId',
            'pumpSwitchChannelId',
            'heatOrColdSourceSwitchChannelId',
            'floodSensorChannelIds',
            'levelSensorChannelIds',
        ]);
        return [
            'channels' => array_values($dependentChannels),
        ];
    }

    public function getItemsThatDependOnVisibility(IODeviceChannel $channel): array {
        $deps = $this->getItemsThatDependOnLocation($channel);
        if ($channel->getType()->getId() === ChannelType::HVAC) {
            $deps['channels'] = [];
        }
        return $deps;
    }

    public function getChannelsToRemoveWith(IODeviceChannel $channel, array &$checkedIds = []): array {
        $checkedIds[] = $channel->getId();
        $channelsToRemove = $this->channelRepository->findBy(
            ['function' => ChannelFunction::ACTION_TRIGGER, 'param1' => $channel->getId()]
        );
        if ($channel->getSubDeviceId() > 0) {
            $channelsFromTheSameSubDevice = $channel->getIoDevice()->getChannels()
                ->filter(fn(IODeviceChannel $ch) => $ch->getSubDeviceId() === $channel->getSubDeviceId())
                ->toArray();
            $channelsToRemove = array_merge($channelsToRemove, $channelsFromTheSameSubDevice);
        }
        $removeMap = [];
        foreach ($channelsToRemove as $channelToRemove) {
            $removeMap[$channelToRemove->getId()] = $channelToRemove;
        }
        foreach ($removeMap as $chId => $ch) {
            if (!in_array($chId, $checkedIds)) {
                foreach ($this->getChannelsToRemoveWith($ch, $checkedIds) as $anotherChannel) {
                    $removeMap[$anotherChannel->getId()] = $anotherChannel;
                }
            }
        }
        unset($removeMap[$channel->getId()]);
        return array_values($removeMap);
    }

    public function clearDependencies(IODeviceChannel $channel): void {
        $this->channelParamConfigTranslator->clearConfig($channel);
        foreach ($channel->getChannelGroups() as $channelGroup) {
            $channelGroup->getChannels()->removeElement($channel);
            if ($channelGroup->getChannels()->isEmpty()) {
                $this->channelGroupDependencies->clearDependencies($channelGroup);
                $this->entityManager->remove($channelGroup);
            } else {
                $this->entityManager->persist($channelGroup);
            }
        }
        foreach ($channel->getSchedules() as $schedule) {
            $this->scheduleManager->delete($schedule);
        }
        foreach ($channel->getDirectLinks() as $directLink) {
            $this->entityManager->remove($directLink);
        }
        foreach ($channel->getSceneOperations() as $sceneOperation) {
            $sceneOperation->getOwningScene()->removeOperation($sceneOperation, $this->entityManager);
        }
        foreach ($channel->getOwnReactions() as $reaction) {
            $this->entityManager->remove($reaction);
        }
        foreach ($channel->getReactions() as $reaction) {
            $this->entityManager->remove($reaction);
        }
        $this->clearActionTriggersThatReferencesSubject($channel);
        foreach ($this->findDependentChannels($channel) as $depChannel) {
            $config = $this->channelParamConfigTranslator->getConfig($depChannel);
            foreach ($config as $key => $value) {
                if (str_ends_with($key, 'ChannelId') && $value === $channel->getId()) {
                    $this->channelParamConfigTranslator->setConfig($depChannel, [$key => null]);
                    $this->entityManager->persist($depChannel);
                }
                if (str_ends_with($key, 'ChannelIds') && is_array($value) && in_array($channel->getId(), $value, true)) {
                    $newIds = ArrayUtils::filter($value, fn(int $channelId) => $channelId !== $channel->getId());
                    $this->channelParamConfigTranslator->setConfig($depChannel, [$key => $newIds]);
                    $this->entityManager->persist($depChannel);
                }
            }
        }
    }

    private function findDependentChannelsRecursive(
        IODeviceChannel $channel,
        array $skipConfigIds = [],
        array &$checkedChannelsIds = []
    ): array {
        $checkedChannelsIds[] = $channel->getId();
        $dependentChannels = $this->findDependentChannels($channel, $skipConfigIds);
        foreach ($dependentChannels as $ch) {
            if (!in_array($ch->getId(), $checkedChannelsIds)) {
                $dependentChannels = array_replace(
                    $dependentChannels,
                    $this->findDependentChannelsRecursive($ch, $skipConfigIds, $checkedChannelsIds)
                );
            }
        }
        unset($dependentChannels[$channel->getId()]);
        return $dependentChannels;
    }

    private function findDependentChannels(IODeviceChannel $channel, array $skipConfigIds = []): array {
        $cacheKey = implode('_', [$channel->getId(), implode('_', $skipConfigIds)]);
        if (array_key_exists($cacheKey, $this->dependencyCache)) {
            return $this->dependencyCache[$cacheKey];
        }

        $dependentChannels = [];
        $config = $this->channelParamConfigTranslator->getConfig($channel);

        foreach ($config as $key => $value) {
            if (str_ends_with($key, 'ChannelId') && is_int($value) && $value > 0 && !in_array($key, $skipConfigIds, true)) {
                $depChannel = $this->entityManager->find(IODeviceChannel::class, $value);
                if ($depChannel) {
                    $dependentChannels[$depChannel->getId()] = $depChannel;
                } else {
                    $this->logger->warning('Zombie relationship detected.', [
                        'channelId' => $channel->getId(),
                        'channelFunctionId' => $channel->getFunction()->getId(),
                        'channelFunctionName' => $channel->getFunction()->getName(),
                        'relatedId' => $value,
                        'relationName' => $key,
                    ]);
                }
            }
        }

        foreach ($this->findActionTriggers($channel) as $atChannel) {
            $dependentChannels[$atChannel->getId()] = $atChannel;
        }

        foreach ($this->getDeviceDependencyIndex($channel->getIoDevice(), $skipConfigIds)[$channel->getId()] ?? [] as $possibleChannel) {
            $dependentChannels[$possibleChannel->getId()] = $possibleChannel;
        }

        $this->dependencyCache[$cacheKey] = $dependentChannels;
        return $dependentChannels;
    }

    private function getDeviceDependencyIndex(IODevice $device, array $skipConfigIds = []): array {
        $cacheKey = $device->getId() . '_' . implode('_', $skipConfigIds);
        if (array_key_exists($cacheKey, $this->deviceDependencyIndexCache)) {
            return $this->deviceDependencyIndexCache[$cacheKey];
        }

        $index = [];
        foreach ($this->channelRepository->findBy(['iodevice' => $device]) as $possibleChannel) {
            $config = $this->channelParamConfigTranslator->getConfig($possibleChannel);
            foreach ($config as $key => $value) {
                if (in_array($key, $skipConfigIds, true)) {
                    continue;
                }
                if (str_ends_with($key, 'ChannelId') && is_int($value) && $value > 0) {
                    $index[$value][$possibleChannel->getId()] = $possibleChannel;
                }
                if (str_ends_with($key, 'ChannelIds') && is_array($value)) {
                    foreach ($value as $channelId) {
                        if (is_int($channelId) && $channelId > 0) {
                            $index[$channelId][$possibleChannel->getId()] = $possibleChannel;
                        }
                    }
                }
            }
        }

        $this->deviceDependencyIndexCache[$cacheKey] = $index;
        return $index;
    }

    private function findActionTriggers(IODeviceChannel $channel): array {
        if (!array_key_exists($channel->getId(), $this->actionTriggersCache)) {
            $this->actionTriggersCache[$channel->getId()] = $this->channelRepository->findActionTriggers($channel);
        }
        return $this->actionTriggersCache[$channel->getId()];
    }
}
