<?php
namespace SuplaBundle\Model\ChannelStateGetter;

use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Supla\SuplaServerAware;

class ImpulseCounterChannelStateGetter implements SingleChannelStateGetter {
    use SuplaServerAware;

    public function getState(IODeviceChannel $channel): array {
        return $this->suplaServer->getImpulseCounterValue($channel);
    }

    public function supportedFunctions(): array {
        return [
            ChannelFunction::ELECTRICITYMETER_IMPULSECOUNTER(),
            ChannelFunction::GASMETER(),
            ChannelFunction::WATERMETER(),
        ];
    }
}
