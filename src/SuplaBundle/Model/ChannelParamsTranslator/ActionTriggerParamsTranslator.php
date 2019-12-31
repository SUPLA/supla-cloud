<?php

namespace SuplaBundle\Model\ChannelParamsTranslator;

use Assert\Assertion;
use SuplaBundle\Entity\EntityUtils;
use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelFunctionAction;
use SuplaBundle\Enums\ChannelFunctionBitsActionTrigger;
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
            'supportedTriggers' => $this->getSupportedTriggers($channel),
            'actions' => new JsonArrayObject($channel->getConfig()['actions'] ?? []),
        ];
    }

    public function setParamsFromConfig(IODeviceChannel $channel, array $config) {
        if (array_key_exists('actions', $config)) {
            Assertion::isArray($config['actions']);
            $actions = $config['actions'];
            $supportedTriggers = $this->getSupportedTriggers($channel);
            Assertion::allInArray(array_keys($actions), $supportedTriggers, '%s trigger is not supported by the hardware.'); // i18n
            $actions = array_map(function (array $action) {
                return $this->adjustAction($action);
            }, $actions);
            $channel->setConfig(array_replace($channel->getConfig(), ['actions' => $actions]));
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
        $params = $this->channelActionExecutor->validateActionParams($subject, $channelFunctionAction, $actionToExecute['params'] ?? []);
        $action['action']['params'] = $params;
        return $action;
    }

    private function getSupportedTriggers(IODeviceChannel $channel): array {
        return ChannelFunctionBitsActionTrigger::getSupportedFeaturesNames($channel->getFlags());
    }
}
