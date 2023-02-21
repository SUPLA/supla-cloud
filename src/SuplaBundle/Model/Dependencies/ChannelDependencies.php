<?php

namespace SuplaBundle\Model\Dependencies;

use Doctrine\ORM\EntityManagerInterface;
use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Model\Schedule\ScheduleManager;
use SuplaBundle\Model\UserConfigTranslator\ChannelParamConfigTranslator;

/**
 * This class is responsible for detecting and possibly clearing all items that rely on the given channel (and its function).
 */
class ChannelDependencies extends ActionableSubjectDependencies {
    /** @var ScheduleManager */
    private $scheduleManager;
    /** @var ChannelGroupDependencies */
    private $channelGroupDependencies;

    public function __construct(
        EntityManagerInterface $entityManager,
        ChannelParamConfigTranslator $channelParamConfigTranslator,
        ScheduleManager $scheduleManager,
        ChannelGroupDependencies $channelGroupDependencies
    ) {
        parent::__construct($entityManager, $channelParamConfigTranslator);
        $this->scheduleManager = $scheduleManager;
        $this->channelGroupDependencies = $channelGroupDependencies;
    }

    public function getDependencies(IODeviceChannel $channel): array {
        return [
            'channelGroups' => $channel->getChannelGroups()->toArray(),
            'directLinks' => $channel->getDirectLinks()->toArray(),
            'schedules' => $channel->getSchedules()->toArray(),
            'sceneOperations' => $channel->getSceneOperations()->toArray(),
            'actionTriggers' => $this->findActionTriggersForSubject($channel)->getValues(),
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
            $sceneOperation->getOwningScene()->removeOperation($sceneOperation, $this->entityManager, $this->suplaServer);
        }
        $this->clearActionTriggersThatReferencesSubject($channel);
    }
}
