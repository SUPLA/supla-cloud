<?php

namespace App\Model\Dependencies;

use App\Entity\ActionableSubject;
use App\Entity\Main\IODevice;
use App\Entity\Main\IODeviceChannel;
use App\Enums\ChannelFunction;
use App\Model\UserConfigTranslator\ActionTriggerParamsTranslator;
use App\Model\UserConfigTranslator\SubjectConfigTranslator;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;

abstract class ActionableSubjectDependencies {

    private array $actionTriggerIndexCache = [];

    public function __construct(
        protected readonly EntityManagerInterface $entityManager,
        protected readonly SubjectConfigTranslator $channelParamConfigTranslator,
        protected readonly ActionTriggerParamsTranslator $actionTriggerParamsTranslator,
    ) {
    }

    public function onlyDependenciesVisibleToUser(array $dependencies): array {
        if ($dependencies['channels'] ?? []) {
            $dependencies['channels'] = array_values(array_filter(
                $dependencies['channels'],
                function (IODeviceChannel $channel) {
                    $config = $this->channelParamConfigTranslator->getConfig($channel);
                    return !($config['hideInChannelsList'] ?? false);
                }
            ));
        }

        return $dependencies;
    }

    public function onlyDependenciesFromOtherDevices(array $dependencies, IODevice $device): array {
        if ($dependencies['channels'] ?? []) {
            $dependencies['channels'] = array_values(array_filter(
                $dependencies['channels'],
                fn(IODeviceChannel $channel) => $channel->getIodevice()->getId() !== $device->getId()
            ));
        }

        return $dependencies;
    }

    protected function clearActionTriggersThatReferencesSubject(ActionableSubject $subject): void {
        foreach ($this->findActionTriggersForSubject($subject) as $actionTrigger) {
            $this->actionTriggerParamsTranslator->removeActionsReferencingSubject($actionTrigger, $subject);
            $this->entityManager->persist($actionTrigger);
        }
    }

    /** @return Collection|IODeviceChannel[] */
    protected function findActionTriggersForSubject(ActionableSubject $subject): Collection {
        $user = $subject->getUser();

        $cacheKey = $user->getId();

        if (!array_key_exists($cacheKey, $this->actionTriggerIndexCache)) {
            $this->actionTriggerIndexCache[$cacheKey] = $this->buildActionTriggerIndex($user);
        }

        $subjectKey = $subject->getOwnSubjectType() . ':' . $subject->getId();

        return new ArrayCollection(
            array_values(
                $this->actionTriggerIndexCache[$cacheKey][$subjectKey] ?? []
            )
        );
    }

    private function buildActionTriggerIndex($user): array {
        $index = [];

        foreach ($user->getChannels() as $channel) {
            if ($channel->getFunction()->getId() !== ChannelFunction::ACTION_TRIGGER) {
                continue;
            }

            $actions = $channel->getUserConfig()['actions'] ?? [];

            foreach ($actions as $action) {
                $subjectType = $action['subjectType'] ?? null;
                $subjectId = $action['subjectId'] ?? null;

                if (!$subjectType || !$subjectId) {
                    continue;
                }

                $key = $subjectType . ':' . $subjectId;

                $index[$key][$channel->getId()] = $channel;
            }
        }

        return $index;
    }
}
