<?php

namespace SuplaBundle\Model\UserConfigTranslator;

use Assert\Assertion;
use OpenApi\Annotations as OA;
use SuplaBundle\Entity\EntityUtils;
use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Enums\ActionableSubjectType;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelFunctionAction;
use SuplaBundle\Model\ChannelActionExecutor\ChannelActionExecutor;
use SuplaBundle\Model\CurrentUserAware;
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
class ActionTriggerParamsTranslator implements ChannelParamTranslator {
    use CurrentUserAware;

    /** @var ActionableSubjectRepository */
    private $subjectRepository;
    /** @var ChannelActionExecutor */
    private $channelActionExecutor;

    public function __construct(ActionableSubjectRepository $subjectRepository, ChannelActionExecutor $channelActionExecutor) {
        $this->subjectRepository = $subjectRepository;
        $this->channelActionExecutor = $channelActionExecutor;
    }

    public function getConfigFromParams(IODeviceChannel $channel): array {
        return [
            'actionTriggerCapabilities' => $channel->getProperties()['actionTriggerCapabilities'] ?? [],
            'disablesLocalOperation' => $channel->getProperties()['disablesLocalOperation'] ?? [],
            'relatedChannelId' => $channel->getParam1() ?: null,
            'hideInChannelsList' => !!$channel->getParam1(),
            'actions' => new JsonArrayObject($channel->getUserConfig()['actions'] ?? []),
        ];
    }

    public function setParamsFromConfig(IODeviceChannel $channel, array $config) {
        if (array_key_exists('actions', $config)) {
            $actions = $config['actions'] ?: [];
            Assertion::isArray($actions);
            $supportedTriggers = $this->getConfigFromParams($channel)['actionTriggerCapabilities'];
            Assertion::allInArray(array_keys($actions), $supportedTriggers, '%s trigger is not supported by the hardware.'); // i18n
            $actions = array_map(function (array $action) {
                return $this->adjustAction($action);
            }, $actions);
            $channel->setUserConfig(array_replace($channel->getUserConfig(), ['actions' => $actions]));
        }
    }

    public function supports(IODeviceChannel $channel): bool {
        return in_array($channel->getFunction()->getId(), [
            ChannelFunction::ACTION_TRIGGER,
        ]);
    }

    private function adjustAction(array $action): array {
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
}
