<?php

namespace SuplaBundle\Model\ChannelParamsTranslator;

use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelFunctionBitsActionTrigger;

class ActionTriggerParamsTranslator implements ChannelParamTranslator {
    public function getConfigFromParams(IODeviceChannel $channel): array {
        return [
            'supportedBehaviors' => ChannelFunctionBitsActionTrigger::getSupportedFeaturesNames($channel->getFlags()),
        ];
    }

    public function setParamsFromConfig(IODeviceChannel $channel, array $config) {
    }

    public function supports(IODeviceChannel $channel): bool {
        return in_array($channel->getFunction()->getId(), [
            ChannelFunction::ACTION_TRIGGER,
        ]);
    }
}
