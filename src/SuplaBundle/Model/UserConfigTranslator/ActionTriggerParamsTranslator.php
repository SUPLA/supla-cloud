<?php

namespace SuplaBundle\Model\UserConfigTranslator;

use Assert\Assertion;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Annotations as OA;
use SuplaBundle\Entity\HasUserConfig;
use SuplaBundle\Entity\Main\PushNotification;
use SuplaBundle\Enums\ActionableSubjectType;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelFunctionAction;
use SuplaBundle\Model\ChannelActionExecutor\ChannelActionExecutor;
use SuplaBundle\Model\CurrentUserAware;
use SuplaBundle\Repository\ActionableSubjectRepository;
use SuplaBundle\Serialization\RequestFiller\SubjectActionFiller;
use SuplaBundle\Utils\JsonArrayObject;

/**
 * @OA\Schema(schema="ChannelConfigActionTrigger",
 *     description="Config for `ACTION_TRIGGER`",
 *     @OA\Property(property="actionTriggerCapabilities", type="array", readOnly=true, description="List of supported triggers. Set only by the device.", @OA\Items(type="string")),
 *     @OA\Property(property="disablesLocalOperation", type="boolean", readOnly=true, description="Tells if enabling the AT disables local function."),
 *     @OA\Property(property="relatedChannelId", type="integer", readOnly=true, description="Paired channel set by the device."),
 *     @OA\Property(property="hideInChannelsList", type="integer", readOnly=true, description="Whether to display the channel in the main channels list (it's false for ATs with paired channel)."),
 *     @OA\Property(property="actions", type="object", description="List of configured AT actions."),
 * )
 */
class ActionTriggerParamsTranslator extends UserConfigTranslator {
    use CurrentUserAware;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly SubjectActionFiller $subjectActionFiller,
        private readonly ChannelActionExecutor $channelActionExecutor,
        private readonly ActionableSubjectRepository $subjectRepository
    ) {
    }

    public function getConfig(HasUserConfig $subject): array {
        return [
            'actionTriggerCapabilities' => $subject->getProperties()['actionTriggerCapabilities'] ?? [],
            'disablesLocalOperation' => $subject->getProperties()['disablesLocalOperation'] ?? [],
            'relatedChannelId' => $subject->getParam1() ?: null,
            'hideInChannelsList' => !!$subject->getParam1(),
            'actions' => new JsonArrayObject($this->getActions($subject)),
        ];
    }

    public function setConfig(HasUserConfig $subject, array $config) {
        if (array_key_exists('actions', $config)) {
            $actions = $config['actions'] ?: [];
            Assertion::isArray($actions);
            $supportedTriggers = $this->getConfig($subject)['actionTriggerCapabilities'];
            Assertion::allInArray(array_keys($actions), $supportedTriggers, '%s trigger is not supported by the hardware.'); // i18n
            $actions = array_map(function (array $action) use ($subject) {
                return $this->adjustAction($subject, $action);
            }, $actions);
            $this->clearOldNotifications($subject->getUserConfigValue('actions', []));
            $subject->setUserConfigValue('actions', $actions);
        }
    }

    public function supports(HasUserConfig $subject): bool {
        return in_array($subject->getFunction()->getId(), [
            ChannelFunction::ACTION_TRIGGER,
        ]);
    }

    private function adjustAction(HasUserConfig $subject, array $action): array {
        Assertion::keyExists($action, 'subjectType');
        $subjectType = ActionableSubjectType::fromString($action['subjectType']);
        Assertion::keyExists($action, 'action');
        $actionToExecute = $action['action'];
        Assertion::keyExists($actionToExecute, 'id');
        $actionDefinition = ['subjectType' => $subjectType->getValue()];
        if ($action['subjectType'] === ActionableSubjectType::OTHER) {
            Assertion::inArray(
                $actionToExecute['id'],
                [ChannelFunctionAction::AT_DISABLE_LOCAL_FUNCTION, ChannelFunctionAction::AT_FORWARD_OUTSIDE]
            );
            $actionDefinition['action'] = ['id' => $actionToExecute['id']];
        } else {
            [$triggerSubject, $channelFunctionAction, $actionParam] = $this->subjectActionFiller->getSubjectAndAction(
                $subject->getUser(),
                array_merge($action, ['actionId' => $actionToExecute['id'], 'actionParam' => $actionToExecute['param'] ?? []])
            );
            if ($triggerSubject instanceof PushNotification) {
                $triggerSubject->setChannel($subject);
                $this->entityManager->persist($triggerSubject);
                $this->entityManager->flush();
            }
            $actionDefinition['subjectId'] = $triggerSubject->getId();
            $actionDefinition['action'] = ['id' => $channelFunctionAction->getId(), 'param' => $actionParam];
        }
        return $actionDefinition;
    }

    private function clearOldNotifications(array $actionsConfig) {
        foreach ($actionsConfig as $action) {
            if ($action['subjectType'] === ActionableSubjectType::NOTIFICATION) {
                $notification = $this->entityManager->find(PushNotification::class, $action['subjectId'] ?? 0);
                if ($notification) {
                    $this->entityManager->remove($notification);
                }
            }
        }
    }

    private function getActions(HasUserConfig $channel): array {
        $triggers = $channel->getUserConfig()['actions'] ?? [];
        return array_map(function (array $triggerDef) use ($channel) {
            if ($triggerDef['action']['param'] ?? false) {
                $subject = $this->subjectRepository->findForUser($channel->getUser(), $triggerDef['subjectType'], $triggerDef['subjectId']);
                $triggerDef['action']['param'] = $this->channelActionExecutor->transformActionParamsForApi(
                    $subject,
                    new ChannelFunctionAction($triggerDef['action']['id']),
                    $triggerDef['action']['param']
                );
            }
            return $triggerDef;
        }, $triggers);
    }
}
