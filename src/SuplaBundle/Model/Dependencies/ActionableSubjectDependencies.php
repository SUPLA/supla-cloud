<?php

namespace SuplaBundle\Model\Dependencies;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use SuplaBundle\Entity\ActionableSubject;
use SuplaBundle\Entity\Main\IODevice;
use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Model\UserConfigTranslator\SubjectConfigTranslator;
use SuplaBundle\Utils\JsonArrayObject;

abstract class ActionableSubjectDependencies {

    /** @var EntityManagerInterface */
    protected $entityManager;

    /** @var SubjectConfigTranslator */
    protected $channelParamConfigTranslator;

    private array $actionTriggerIndexCache = [];

    public function __construct(
        EntityManagerInterface $entityManager,
        SubjectConfigTranslator $channelParamConfigTranslator
    ) {
        $this->entityManager = $entityManager;
        $this->channelParamConfigTranslator = $channelParamConfigTranslator;
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
            $config = $this->channelParamConfigTranslator->getConfig($actionTrigger);

            $actions = (new JsonArrayObject($config['actions'] ?? []))->toArray();

            $config['actions'] = array_filter(
                $actions,
                function (array $action) use ($subject) {
                    return !(
                        ($action['subjectType'] ?? null) === $subject->getOwnSubjectType()
                        && ($action['subjectId'] ?? null) === $subject->getId()
                    );
                }
            );

            $this->channelParamConfigTranslator->setConfig($actionTrigger, $config);
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
