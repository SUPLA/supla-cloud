<?php

namespace SuplaBundle\Model\Dependencies;

use Doctrine\ORM\EntityManagerInterface;
use SuplaBundle\Entity\IODeviceChannelGroup;
use SuplaBundle\Model\ChannelParamsTranslator\ChannelParamConfigTranslator;
use SuplaBundle\Model\Schedule\ScheduleManager;

/**
 * This class is responsible for detecting and possibly clearing all items that rely on the given channel (and its function).
 */
class ChannelGroupDependencies extends ActionableSubjectDependencies {
    /** @var ScheduleManager */
    private $scheduleManager;

    public function __construct(
        EntityManagerInterface $entityManager,
        ChannelParamConfigTranslator $channelParamConfigTranslator,
        ScheduleManager $scheduleManager
    ) {
        parent::__construct($entityManager, $channelParamConfigTranslator);
        $this->scheduleManager = $scheduleManager;
    }

    public function getDependencies(IODeviceChannelGroup $channelGroup): array {
        return [
            'directLinks' => $channelGroup->getDirectLinks()->toArray(),
            'schedules' => $channelGroup->getSchedules()->toArray(),
            'sceneOperations' => $channelGroup->getSceneOperations()->toArray(),
            'actionTriggers' => $this->findActionTriggersForSubject($channelGroup)->getValues(),
        ];
    }

    public function clearDependencies(IODeviceChannelGroup $channelGroup): void {
        foreach ($channelGroup->getSchedules() as $schedule) {
            $this->scheduleManager->delete($schedule);
        }
        foreach ($channelGroup->getDirectLinks() as $directLink) {
            $this->entityManager->remove($directLink);
        }
        foreach ($channelGroup->getSceneOperations() as $sceneOperation) {
            $sceneOperation->getOwningScene()->removeOperation($sceneOperation, $this->entityManager);
        }
        $this->clearActionTriggersThatReferencesSubject($channelGroup);
    }
}