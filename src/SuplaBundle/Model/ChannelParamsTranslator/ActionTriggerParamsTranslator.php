<?php

namespace SuplaBundle\Model\ChannelParamsTranslator;

use Assert\Assertion;
use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelFunctionBitsActionTrigger;
use SuplaBundle\Model\ChannelActionExecutor\ChannelActionExecutor;
use SuplaBundle\Repository\ActionableSubjectRepository;

class ActionTriggerParamsTranslator implements ChannelParamTranslator {
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
            'supportedTriggers' => ChannelFunctionBitsActionTrigger::getSupportedFeaturesNames($channel->getFlags()),
            'actions' => new \stdClass(),
        ];
    }

    public function setParamsFromConfig(IODeviceChannel $channel, array $config) {
        if (array_key_exists('actions', $config)) {
            Assertion::isArray($config['actions']);
            $channel->setConfig(array_replace($channel->getConfig(), ['actions' => $config['actions']]));
        }
    }

    public function supports(IODeviceChannel $channel): bool {
        return in_array($channel->getFunction()->getId(), [
            ChannelFunction::ACTION_TRIGGER,
        ]);
    }
}
