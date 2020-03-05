<?php
namespace SuplaBundle\Model\ChannelStateGetter;

use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelType;
use SuplaBundle\Supla\SuplaServerAware;

class ImpulseCounterChannelStateGetter implements SingleChannelStateGetter {
    use SuplaServerAware;

    public function getState(IODeviceChannel $channel): array {
        if ($channel->getType()->getId() === ChannelType::IMPULSECOUNTER) {
            return $this->suplaServer->getImpulseCounterValue($channel);
        } else {
            return [];
        }
    }

    public function supportedFunctions(): array {
        return [
            ChannelFunction::ELECTRICITYMETER(),
            ChannelFunction::GASMETER(),
            ChannelFunction::WATERMETER(),
            ChannelFunction::HEATMETER(),
        ];
    }
}
