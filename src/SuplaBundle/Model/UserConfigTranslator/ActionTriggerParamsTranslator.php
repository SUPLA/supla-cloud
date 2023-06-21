<?php

namespace SuplaBundle\Model\UserConfigTranslator;

use Assert\Assertion;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Annotations as OA;
use SuplaBundle\Entity\EntityUtils;
use SuplaBundle\Entity\HasUserConfig;
use SuplaBundle\Entity\Main\PushNotification;
use SuplaBundle\Enums\ActionableSubjectType;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelFunctionAction;
use SuplaBundle\Model\ChannelActionExecutor\ChannelActionExecutor;
use SuplaBundle\Model\CurrentUserAware;
use SuplaBundle\Repository\AccessIdRepository;
use SuplaBundle\Repository\ActionableSubjectRepository;
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
class ActionTriggerParamsTranslator implements UserConfigTranslator {
    use CurrentUserAware;

    /** @var ActionableSubjectRepository */
    private $subjectRepository;
    /** @var ChannelActionExecutor */
    private $channelActionExecutor;
    /** @var AccessIdRepository */
    private $aidRepository;
    /** @var EntityManagerInterface */
    private $entityManager;

    public function __construct(
        ActionableSubjectRepository $subjectRepository,
        ChannelActionExecutor $channelActionExecutor,
        AccessIdRepository $accessIdRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->subjectRepository = $subjectRepository;
        $this->channelActionExecutor = $channelActionExecutor;
        $this->aidRepository = $accessIdRepository;
        $this->entityManager = $entityManager;
    }

    public function getConfig(HasUserConfig $subject): array {
        return [
            'actionTriggerCapabilities' => $subject->getProperties()['actionTriggerCapabilities'] ?? [],
            'disablesLocalOperation' => $subject->getProperties()['disablesLocalOperation'] ?? [],
            'relatedChannelId' => $subject->getParam1() ?: null,
            'hideInChannelsList' => !!$subject->getParam1(),
            'actions' => new JsonArrayObject($subject->getUserConfig()['actions'] ?? []),
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
            $subject->setUserConfig(array_replace($subject->getUserConfig(), ['actions' => $actions]));
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
        $channelFunctionAction = ChannelFunctionAction::fromString($actionToExecute['id']);
        $actionDefinition = ['subjectType' => $subjectType->getValue()];
        if ($action['subjectType'] === ActionableSubjectType::OTHER) {
            Assertion::inArray(
                $actionToExecute['id'],
                [ChannelFunctionAction::AT_DISABLE_LOCAL_FUNCTION, ChannelFunctionAction::AT_FORWARD_OUTSIDE]
            );
            $actionDefinition['action'] = ['id' => $actionToExecute['id']];
        } elseif ($action['subjectType'] === ActionableSubjectType::NOTIFICATION) {
            $notification = new PushNotification($this->getCurrentUser());
            $params = $this->channelActionExecutor->validateActionParams($notification, $channelFunctionAction, $actionToExecute['param'] ?? []);
            $notification->initFromValidatedActionParams($params, $this->aidRepository);
            $notification->setChannel($subject);
            $this->entityManager->persist($notification);
            $this->entityManager->flush();
            $actionDefinition['subjectId'] = $notification->getId();
            $actionDefinition['action'] = ['id' => ChannelFunctionAction::SEND, 'param' => $params];
        } else {
            $user = $this->getCurrentUser();
            Assertion::keyExists($action, 'subjectId');
            $subject = $this->subjectRepository->findForUser($user, $action['subjectType'], $action['subjectId']);
            Assertion::inArray(
                $channelFunctionAction->getId(),
                EntityUtils::mapToIds($subject->getPossibleActions()),
                'Cannot execute the requested action on given subject.'
            );
            $params = $this->channelActionExecutor->validateActionParams($subject, $channelFunctionAction, $actionToExecute['param'] ?? []);
            $actionDefinition['subjectId'] = $subject->getId();
            $actionDefinition['action'] = ['id' => $channelFunctionAction->getId(), 'param' => $params];
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
}
