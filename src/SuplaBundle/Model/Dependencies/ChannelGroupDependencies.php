<?php

namespace SuplaBundle\Model\Dependencies;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Entity\IODeviceChannelGroup;
use SuplaBundle\Enums\ActionableSubjectType;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Model\ChannelParamsTranslator\ChannelParamConfigTranslator;
use SuplaBundle\Model\Schedule\ScheduleManager;

/**
 * This class is responsible for detecting and possibly clearing all items that rely on the given channel (and its function).
 */
class ChannelGroupDependencies {
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

    public function getDependencies(IODeviceChannelGroup $channelGroup): array {
        return [
            'directLinks' => $channelGroup->getDirectLinks()->toArray(),
            'schedules' => $channelGroup->getSchedules()->toArray(),
            'sceneOperations' => $channelGroup->getSceneOperations()->toArray(),
            'actionTriggers' => $this->findActionTriggersForChannelGroup($channelGroup)->getValues(),
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
        foreach ($this->findActionTriggersForChannelGroup($channelGroup) as $actionTrigger) {
            $config = $this->channelParamConfigTranslator->getConfigFromParams($actionTrigger);
            $actions = $actionTrigger->getUserConfig()['actions'] ?? [];
            $config['actions'] = array_filter($actions, function (array $action) use ($channelGroup) {
                $referencesThisSubject = $action['subjectType'] === ActionableSubjectType::CHANNEL_GROUP
                    && $action['subjectId'] === $channelGroup->getId();
                return !$referencesThisSubject;
            });
            $this->channelParamConfigTranslator->setParamsFromConfig($actionTrigger, $config);
            $this->entityManager->persist($actionTrigger);
        }
    }

    /** @return Collection|IODeviceChannel[] */
    private function findActionTriggersForChannelGroup(IODeviceChannelGroup $channelGroup): Collection {
        return $channelGroup->getUser()
            ->getChannels()
            ->filter(function (IODeviceChannel $ch) {
                return $ch->getFunction()->getId() === ChannelFunction::ACTION_TRIGGER;
            })
            ->filter(function (IODeviceChannel $ch) use ($channelGroup) {
                return !!array_filter($ch->getUserConfig()['actions'] ?? [], function ($action) use ($channelGroup) {
                    return $action['subjectType'] === ActionableSubjectType::CHANNEL_GROUP
                        && $action['subjectId'] === $channelGroup->getId();
                });
            });
    }
}
