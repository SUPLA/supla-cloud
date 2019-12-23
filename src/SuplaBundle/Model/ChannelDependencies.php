<?php

namespace SuplaBundle\Model;

use Doctrine\ORM\EntityManagerInterface;
use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Model\ChannelParamsTranslator\ChannelParamConfigTranslator;
use SuplaBundle\Model\Schedule\ScheduleManager;

/**
 * This class is responsible for detecting and possibly clearing all items that rely on the given channel (and its function).
 */
class ChannelDependencies {
    /** @var EntityManagerInterface */
    private $entityManager;
    /** @var ScheduleManager */
    private $scheduleManager;
    /** @var ChannelParamConfigTranslator */
    private $channelParamConfigTranslator;

    public function __construct(
        EntityManagerInterface $entityManager,
        ChannelParamConfigTranslator $channelParamConfigTranslator,
        ScheduleManager $scheduleManager
    ) {
        $this->entityManager = $entityManager;
        $this->scheduleManager = $scheduleManager;
        $this->channelParamConfigTranslator = $channelParamConfigTranslator;
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
        $this->channelParamConfigTranslator->clearConfig($channel);
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
