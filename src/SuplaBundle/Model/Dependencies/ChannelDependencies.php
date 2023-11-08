<?php

namespace SuplaBundle\Model\Dependencies;

use Doctrine\ORM\EntityManagerInterface;
use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Enums\ChannelType;
use SuplaBundle\Model\Schedule\ScheduleManager;
use SuplaBundle\Model\UserConfigTranslator\SubjectConfigTranslator;
use SuplaBundle\Repository\IODeviceChannelRepository;

/**
 * This class is responsible for detecting and possibly clearing all items that rely on the given channel (and its function).
 */
class ChannelDependencies extends ActionableSubjectDependencies {
    /** @var ScheduleManager */
    private $scheduleManager;
    /** @var ChannelGroupDependencies */
    private $channelGroupDependencies;
    /** @var IODeviceChannelRepository */
    private $channelRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        SubjectConfigTranslator $channelParamConfigTranslator,
        ScheduleManager $scheduleManager,
        ChannelGroupDependencies $channelGroupDependencies,
        IODeviceChannelRepository $channelRepository
    ) {
        parent::__construct($entityManager, $channelParamConfigTranslator);
        $this->scheduleManager = $scheduleManager;
        $this->channelGroupDependencies = $channelGroupDependencies;
        $this->channelRepository = $channelRepository;
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
        return [
            'channels' => array_values($this->findDependentChannelsRecursive($channel)),
        ];
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
    }

    private function findDependentChannelsRecursive(IODeviceChannel $channel, array $checkedChannelsIds = []): array {
        $checkedChannelsIds[] = $channel->getId();
        $dependentChannels = $this->findDependentChannels($channel);
        foreach ($dependentChannels as $ch) {
            if (!in_array($ch->getId(), $checkedChannelsIds)) {
                $dependentChannels = array_replace($dependentChannels, $this->findDependentChannelsRecursive($ch, $checkedChannelsIds));
            }
        }
        if (isset($dependentChannels[$channel->getId()])) {
            unset($dependentChannels[$channel->getId()]);
        }
        return $dependentChannels;
    }

    private function findDependentChannels(IODeviceChannel $channel): array {
        $config = $this->channelParamConfigTranslator->getConfig($channel);
        $dependentChannels = [];
        foreach ($config as $key => $value) {
            if ((strpos($key, 'ChannelId') > 0) && is_int($value) && $value > 0) {
                $depChannel = $this->entityManager->find(IODeviceChannel::class, $value);
                $dependentChannels[$depChannel->getId()] = $depChannel;
            }
        }
        foreach ($this->channelRepository->findActionTriggers($channel) as $atChannel) {
            $dependentChannels[$atChannel->getId()] = $atChannel;
        }
        foreach ($this->channelRepository->findBy(['type' => ChannelType::HVAC]) as $possibleChannel) {
            $config = $this->channelParamConfigTranslator->getConfig($possibleChannel);
            foreach ($config as $key => $value) {
                if ((strpos($key, 'ChannelId') > 0) && $value === $channel->getId()) {
                    $dependentChannels[$possibleChannel->getId()] = $possibleChannel;
                }
            }
        }
        return $dependentChannels;
    }
}
