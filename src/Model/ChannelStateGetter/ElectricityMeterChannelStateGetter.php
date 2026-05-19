<?php
namespace App\Model\ChannelStateGetter;

use App\Entity\Main\IODeviceChannel;
use App\Enums\ChannelFunction;
use App\Supla\SuplaServerAware;

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
