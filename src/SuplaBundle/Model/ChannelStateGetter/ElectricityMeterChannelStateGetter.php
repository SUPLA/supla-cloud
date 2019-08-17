<?php
namespace SuplaBundle\Model\ChannelStateGetter;

use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelType;
use SuplaBundle\Supla\SuplaServerAware;

class ElectricityMeterChannelStateGetter implements SingleChannelStateGetter {
    use SuplaServerAware;

    public function getState(IODeviceChannel $channel): array {
        if ($channel->getType()->getId() === ChannelType::ELECTRICITYMETER) {
            return $this->suplaServer->getElectricityMeterValue($channel);
        } else {
            return [];
        }
    }

    public function supportedFunctions(): array {
        return [
            ChannelFunction::ELECTRICITYMETER(),
        ];
    }
}
