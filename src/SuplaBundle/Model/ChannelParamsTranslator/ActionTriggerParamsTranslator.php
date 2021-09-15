<?php

namespace SuplaBundle\Model\ChannelParamsTranslator;

use Assert\Assertion;
use SuplaBundle\Entity\EntityUtils;
use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Enums\ActionableSubjectType;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelFunctionAction;
use SuplaBundle\Model\ChannelActionExecutor\ChannelActionExecutor;
use SuplaBundle\Model\CurrentUserAware;
use SuplaBundle\Repository\ActionableSubjectRepository;
use SuplaBundle\Utils\JsonArrayObject;

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
            'actionTriggerCapabilities' => $channel->getInternalConfig()['actionTriggerCapabilities'] ?? [],
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
        Assertion::keyExists($action, 'subjectId');
        Assertion::keyExists($action, 'action');
        $user = $this->getCurrentUser();
        $subject = $this->subjectRepository->findForUser($user, $action['subjectType'], $action['subjectId']);
        $actionToExecute = $action['action'];
        Assertion::keyExists($actionToExecute, 'id');
        $channelFunctionAction = ChannelFunctionAction::fromString($actionToExecute['id']);
        Assertion::inArray(
            $channelFunctionAction->getId(),
            EntityUtils::mapToIds($subject->getFunction()->getPossibleActions()),
            'Cannot execute the requested action on given subject.'
        );
        $params = $this->channelActionExecutor->validateActionParams($subject, $channelFunctionAction, $actionToExecute['param'] ?? []);
        return [
            'subjectId' => $subject->getId(),
            'subjectType' => ActionableSubjectType::forEntity($subject)->getValue(),
            'action' => [
                'id' => $channelFunctionAction->getId(),
                'param' => $params,
            ],
        ];
    }
}
