<?php

namespace SuplaBundle\Model\Dependencies;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use SuplaBundle\Entity\ActionableSubject;
use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Model\UserConfigTranslator\SubjectConfigTranslator;
use SuplaBundle\Supla\SuplaServerAware;

abstract class ActionableSubjectDependencies {
    use SuplaServerAware;

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

    protected function clearActionTriggersThatReferencesSubject(ActionableSubject $subject): void {
        foreach ($this->findActionTriggersForSubject($subject) as $actionTrigger) {
            $config = $this->channelParamConfigTranslator->getConfig($actionTrigger);
            $actions = $actionTrigger->getUserConfig()['actions'] ?? [];
            $config['actions'] = array_filter($actions, function (array $action) use ($subject) {
                $referencesThisSubject = $action['subjectType'] === $subject->getSubjectType()
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
                    return $action['subjectType'] === $subject->getSubjectType()
                        && $action['subjectId'] === $subject->getId();
                });
            });
    }
}
