<?php
namespace SuplaBundle\Model\ChannelStateGetter;

use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Supla\SuplaServerAware;

class ElectricityMeterChannelStateGetter implements SingleChannelStateGetter {
    use SuplaServerAware;

    public function getState(IODeviceChannel $channel): array {
        return $this->suplaServer->getElectricityMeterValue($channel);
    }

    public function supportedFunctions(): array {
        return [
            ChannelFunction::ELECTRICITYMETER(),
        ];
    }
}
