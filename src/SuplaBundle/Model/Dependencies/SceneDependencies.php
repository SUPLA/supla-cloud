<?php

namespace SuplaBundle\Model\Dependencies;

use Doctrine\ORM\EntityManagerInterface;
use SuplaBundle\Entity\Main\Scene;
use SuplaBundle\Model\Schedule\ScheduleManager;
use SuplaBundle\Model\UserConfigTranslator\SubjectConfigTranslator;

/**
 * This class is responsible for detecting and possibly clearing all items that depend on the given scene.
 */
class SceneDependencies extends ActionableSubjectDependencies {
    /** @var ScheduleManager */
    private $scheduleManager;

    public function __construct(
        EntityManagerInterface $entityManager,
        SubjectConfigTranslator $channelParamConfigTranslator,
        ScheduleManager $scheduleManager
    ) {
        parent::__construct($entityManager, $channelParamConfigTranslator);
        $this->scheduleManager = $scheduleManager;
    }

    public function getDependencies(Scene $scene): array {
        return [
            'directLinks' => $scene->getDirectLinks()->toArray(),
            'schedules' => $scene->getSchedules()->toArray(),
            'sceneOperations' => $scene->getOperationsThatReferToThisScene()->toArray(),
            'reactions' => $scene->getReactions()->toArray(),
            'actionTriggers' => $this->findActionTriggersForSubject($scene)->getValues(),
        ];
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
    }
}
