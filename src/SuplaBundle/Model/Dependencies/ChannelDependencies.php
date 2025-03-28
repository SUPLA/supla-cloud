<?php

namespace SuplaBundle\Model\Dependencies;

use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelType;
use SuplaBundle\Model\Schedule\ScheduleManager;
use SuplaBundle\Model\UserConfigTranslator\SubjectConfigTranslator;
use SuplaBundle\Repository\IODeviceChannelRepository;
use SuplaBundle\Utils\ArrayUtils;

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
                if (str_ends_with($key, 'ChannelIds') && in_array($channel->getId(), $value, true)) {
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
        if (isset($dependentChannels[$channel->getId()])) {
            unset($dependentChannels[$channel->getId()]);
        }
        return $dependentChannels;
    }

    private function findDependentChannels(IODeviceChannel $channel, array $skipConfigIds = []): array {
        $cacheKey = implode('_', [$channel->getId(), implode('_', $skipConfigIds)]);
        if (array_key_exists($cacheKey, $this->dependencyCache)) {
            return $this->dependencyCache[$cacheKey];
        }
        $config = $this->channelParamConfigTranslator->getConfig($channel);
        $dependentChannels = [];
        foreach ($config as $key => $value) {
            if (str_ends_with($key, 'ChannelId') && is_int($value) && $value > 0 && !in_array($key, $skipConfigIds)) {
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
        foreach ($this->channelRepository->findActionTriggers($channel) as $atChannel) {
            $dependentChannels[$atChannel->getId()] = $atChannel;
        }
        $possibleDeviceRelationFilters = ['iodevice' => $channel->getIoDevice()];
        foreach ($this->channelRepository->findBy($possibleDeviceRelationFilters) as $possibleChannel) {
            $config = $this->channelParamConfigTranslator->getConfig($possibleChannel);
            foreach ($config as $key => $value) {
                if (str_ends_with($key, 'ChannelId') && $value === $channel->getId() && !in_array($key, $skipConfigIds)) {
                    $dependentChannels[$possibleChannel->getId()] = $possibleChannel;
                }
                if (str_ends_with($key, 'ChannelIds') && in_array($channel->getId(), $value, true) && !in_array($key, $skipConfigIds)) {
                    $dependentChannels[$possibleChannel->getId()] = $possibleChannel;
                }
            }
        }
        $this->dependencyCache[$cacheKey] = $dependentChannels;
        return $dependentChannels;
    }
}
