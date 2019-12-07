<?php

namespace SuplaBundle\Model;

use Doctrine\ORM\EntityManagerInterface;
use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Model\ChannelParamsUpdater\ChannelParamsUpdater;
use SuplaBundle\Model\Schedule\ScheduleManager;

/**
 * This class is responsible for detecting and possibly clearing all items that rely on the given channel (and its function).
 */
class ChannelDependencies {
    /** @var ChannelParamsUpdater */
    private $channelParamsUpdater;
    /** @var EntityManagerInterface */
    private $entityManager;
    /** @var ScheduleManager */
    private $scheduleManager;

    public function __construct(
        EntityManagerInterface $entityManager,
        ChannelParamsUpdater $channelParamsUpdater,
        ScheduleManager $scheduleManager
    ) {
        $this->entityManager = $entityManager;
        $this->channelParamsUpdater = $channelParamsUpdater;
        $this->scheduleManager = $scheduleManager;
    }

    public function getDependencies(IODeviceChannel $channel): array {
        return [
            'channelGroups' => $channel->getChannelGroups()->toArray(),
            'directLinks' => $channel->getDirectLinks()->toArray(),
            'schedules' => $channel->getSchedules()->toArray(),
            'sceneOperations' => $channel->getSceneOperations()->toArray(),
        ];
    }

    public function clearDependencies(IODeviceChannel $channel): void {
        // clears all paired channels that are possibly made with the one that is being deleted
        $this->channelParamsUpdater->updateChannelParams($channel, new IODeviceChannel());
        foreach ($channel->getChannelGroups() as $channelGroup) {
            $channelGroup->removeChannel($channel, $this->entityManager);
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
    }
}
