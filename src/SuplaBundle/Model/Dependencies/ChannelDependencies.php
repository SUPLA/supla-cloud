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

    public function getDependencies(IODeviceChannel $channel): array {
        return [
            'channels' => $this->findDependentChannels($channel),
            'channelGroups' => $channel->getChannelGroups()->toArray(),
            'directLinks' => $channel->getDirectLinks()->toArray(),
            'schedules' => $channel->getSchedules()->toArray(),
            'sceneOperations' => $channel->getSceneOperations()->toArray(),
            'actionTriggers' => $this->findActionTriggersForSubject($channel)->getValues(),
            'ownReactions' => $channel->getOwnReactions()->toArray(),
            'reactions' => $channel->getReactions()->toArray(),
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

    private function findDependentChannels(IODeviceChannel $channel): array {
        $config = $this->channelParamConfigTranslator->getConfig($channel);
        $channels = [];
        foreach ($config as $key => $value) {
            if ((strpos($key, 'ChannelId') > 0) && is_int($value) && $value > 0) {
                $channel = $this->entityManager->find(IODeviceChannel::class, $value);
                $channels[$channel->getId()] = $channel;
            }
        }
        foreach ($this->channelRepository->findActionTriggers($channel) as $atChannel) {
            $channels[$atChannel->getId()] = $atChannel;
        }
        foreach ($this->channelRepository->findBy(['type' => ChannelType::HVAC]) as $possibleChannel) {
            $config = $this->channelParamConfigTranslator->getConfig($possibleChannel);
            foreach ($config as $key => $value) {
                if ((strpos($key, 'ChannelId') > 0) && $value === $channel->getId()) {
                    $channels[$possibleChannel->getId()] = $possibleChannel;
                }
            }
        }
        return array_values($channels);
    }
}
