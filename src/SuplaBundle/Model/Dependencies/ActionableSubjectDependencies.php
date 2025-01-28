<?php

namespace SuplaBundle\Model\Dependencies;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use SuplaBundle\Entity\ActionableSubject;
use SuplaBundle\Entity\Main\IODevice;
use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Model\UserConfigTranslator\SubjectConfigTranslator;

abstract class ActionableSubjectDependencies {

    /** @var EntityManagerInterface */
    protected $entityManager;
    /** @var SubjectConfigTranslator */
    protected $channelParamConfigTranslator;

    public function __construct(
        EntityManagerInterface $entityManager,
        SubjectConfigTranslator $channelParamConfigTranslator
    ) {
        $this->entityManager = $entityManager;
        $this->channelParamConfigTranslator = $channelParamConfigTranslator;
    }

    public function onlyDependenciesVisibleToUser(array $dependencies): array {
        if ($dependencies['channels'] ?? []) {
            $dependencies['channels'] = array_values(array_filter($dependencies['channels'], function (IODeviceChannel $channel) {
                $config = $this->channelParamConfigTranslator->getConfig($channel);
                return !($config['hideInChannelsList'] ?? false);
            }));
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
            $config = $this->channelParamConfigTranslator->getConfig($actionTrigger);
            $actions = $actionTrigger->getUserConfig()['actions'] ?? [];
            $config['actions'] = array_filter($actions, function (array $action) use ($subject) {
                $referencesThisSubject = $action['subjectType'] === $subject->getOwnSubjectType()
                    && $action['subjectId'] === $subject->getId();
                return !$referencesThisSubject;
            });
            $this->channelParamConfigTranslator->setConfig($actionTrigger, $config);
            $this->entityManager->persist($actionTrigger);
        }
    }

    /** @return Collection|\SuplaBundle\Entity\Main\IODeviceChannel[] */
    protected function findActionTriggersForSubject(ActionableSubject $subject): Collection {
        return $subject->getUser()
            ->getChannels()
            ->filter(function (IODeviceChannel $ch) {
                return $ch->getFunction()->getId() === ChannelFunction::ACTION_TRIGGER;
            })
            ->filter(function (IODeviceChannel $ch) use ($subject) {
                return !!array_filter($ch->getUserConfig()['actions'] ?? [], function ($action) use ($subject) {
                    return $action['subjectType'] === $subject->getOwnSubjectType()
                        && $action['subjectId'] === $subject->getId();
                });
            });
    }
}
