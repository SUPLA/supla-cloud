<?php

namespace SuplaBundle\Model\Dependencies;

use Doctrine\ORM\EntityManagerInterface;
use SuplaBundle\Entity\Scene;
use SuplaBundle\Model\ChannelParamsTranslator\ChannelParamConfigTranslator;
use SuplaBundle\Model\Schedule\ScheduleManager;

/**
 * This class is responsible for detecting and possibly clearing all items that rely on the given scene.
 */
class SceneDependencies extends ActionableSubjectDependencies {
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

    public function getDependencies(Scene $scene): array {
        return [
            'directLinks' => $scene->getDirectLinks()->toArray(),
            'schedules' => $scene->getSchedules()->toArray(),
            'sceneOperations' => $scene->getOperationsThatReferToThisScene()->toArray(),
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
            $sceneOperation->getOwningScene()->removeOperation($sceneOperation, $this->entityManager, $this->suplaServer);
        }
        $this->clearActionTriggersThatReferencesSubject($scene);
    }
}
