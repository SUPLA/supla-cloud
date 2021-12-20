<?php

namespace SuplaBundle\Model\ChannelParamsTranslator;

use Assert\Assertion;
use OpenApi\Annotations as OA;
use SuplaBundle\Entity\EntityUtils;
use SuplaBundle\Entity\IODeviceChannel;
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
 *     @OA\Property(property="actions", type="array", description="List of configured AT actions.", @OA\Items(
 *       @OA\Property(property="action",
 *          @OA\Property(property="id", ref="#/components/schemas/ChannelFunctionActionEnumNames"),
 *          @OA\Property(property="param", ref="#/components/schemas/ChannelActionParams"),
 *       ),
 *       @OA\Property(property="subjectType", ref="#/components/schemas/ActionableSubjectTypeNames"),
 *       @OA\Property(property="subjectId", type="integer"),
 *     )),
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
            Assertion::eq($actionToExecute['id'], ChannelFunctionAction::GENERIC);
            $params = array_intersect_key($actionToExecute['param'] ?? [], ['action' => '']);
            Assertion::keyExists($params, 'action');
            Assertion::inArray($params['action'], ['disableLocalFunction', 'publishToIntegrations', 'copyChannelState']);
            if ($params['action'] === 'copyChannelState') {
                $params = array_intersect_key(
                    $actionToExecute['param'],
                    ['action' => '', 'sourceChannelId' => '', 'subjectType' => '', 'subjectId' => '']
                );
                Assertion::count($params, 4, 'Invalid copy state definition.');
                if ($params['subjectType'] === ActionableSubjectType::CHANNEL) {
                    Assertion::notEq($params['sourceChannelId'], $params['subjectId'], 'Source and target channel must be different.');
                }
                $sourceChannel = $this->subjectRepository
                    ->findForUser($this->getCurrentUser(), ActionableSubjectType::CHANNEL, $params['sourceChannelId']);
                $target = $this->subjectRepository->findForUser($this->getCurrentUser(), $params['subjectType'], $params['subjectId']);
                Assertion::notNull($sourceChannel, 'Invalid source channel.');
                Assertion::notNull($target, 'Invalid target subject.');
            }
            $actionDefinition['action'] = ['id' => ChannelFunctionAction::GENERIC, 'param' => $params];
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
