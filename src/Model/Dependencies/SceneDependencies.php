<?php

namespace App\Model\Dependencies;

use App\Entity\Main\Scene;
use App\Model\Schedule\ScheduleManager;
use App\Model\UserConfigTranslator\SubjectConfigTranslator;
use Doctrine\ORM\EntityManagerInterface;

/**
 * This class is responsible for detecting and possibly clearing all items that depend on the given scene.
 */
class SceneDependencies extends ActionableSubjectDependencies {
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

    public function getDependencies(Scene $scene): array {
        $cacheKey = $scene->getId();

        if ($cacheKey && array_key_exists($cacheKey, $this->dependenciesCache)) {
            return $this->dependenciesCache[$cacheKey];
        }

        $dependencies = [
            'directLinks' => $scene->getDirectLinks()->toArray(),
            'schedules' => $scene->getSchedules()->toArray(),
            'sceneOperations' => $scene->getOperationsThatReferToThisScene()->toArray(),
            'reactions' => $scene->getReactions()->toArray(),
            'actionTriggers' => $this->findActionTriggersForSubject($scene)->getValues(),
        ];

        if ($cacheKey) {
            $this->dependenciesCache[$cacheKey] = $dependencies;
        }

        return $dependencies;
    }

    public function clearDependencies(Scene $scene): void {
        foreach ($scene->getSchedules() as $schedule) {
            $this->scheduleManager->delete($schedule);
        }

        foreach ($scene->getDirectLinks() as $directLink) {
            $this->entityManager->remove($directLink);
        }

        foreach ($scene->getOperationsThatReferToThisScene() as $sceneOperation) {
            $sceneOperation->getOwningScene()->removeOperation($sceneOperation, $this->entityManager);
        }

        foreach ($scene->getReactions() as $reaction) {
            $this->entityManager->remove($reaction);
        }

        $this->clearActionTriggersThatReferencesSubject($scene);

        if ($scene->getId()) {
            unset($this->dependenciesCache[$scene->getId()]);
        }
    }
}
