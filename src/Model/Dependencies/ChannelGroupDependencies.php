<?php

namespace App\Model\Dependencies;

use App\Entity\Main\IODeviceChannelGroup;
use App\Model\Schedule\ScheduleManager;
use App\Model\UserConfigTranslator\SubjectConfigTranslator;
use Doctrine\ORM\EntityManagerInterface;

/**
 * This class is responsible for detecting and possibly clearing all items that depend on the given channel group.
 */
class ChannelGroupDependencies extends ActionableSubjectDependencies {
    /** @var ScheduleManager */
    private $scheduleManager;

    private array $dependenciesCache = [];

    public function __construct(
        EntityManagerInterface $entityManager,
        SubjectConfigTranslator $channelParamConfigTranslator,
        ScheduleManager $scheduleManager
    ) {
        parent::__construct($entityManager, $channelParamConfigTranslator);
        $this->scheduleManager = $scheduleManager;
    }

    public function getDependencies(IODeviceChannelGroup $channelGroup): array {
        $cacheKey = $channelGroup->getId();

        if ($cacheKey && array_key_exists($cacheKey, $this->dependenciesCache)) {
            return $this->dependenciesCache[$cacheKey];
        }

        $dependencies = [
            'directLinks' => $channelGroup->getDirectLinks()->toArray(),
            'schedules' => $channelGroup->getSchedules()->toArray(),
            'sceneOperations' => $channelGroup->getSceneOperations()->toArray(),
            'actionTriggers' => $this->findActionTriggersForSubject($channelGroup)->getValues(),
            'reactions' => $channelGroup->getReactions()->toArray(),
        ];
        if ($cacheKey) {
            $this->dependenciesCache[$cacheKey] = $dependencies;
        }
        return $dependencies;
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
        foreach ($channelGroup->getReactions() as $reaction) {
            $this->entityManager->remove($reaction);
        }
        $this->clearActionTriggersThatReferencesSubject($channelGroup);
        if ($channelGroup->getId()) {
            unset($this->dependenciesCache[$channelGroup->getId()]);
        }
    }
}
