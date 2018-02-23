<?php
namespace SuplaApiBundle\Model\ChannelStateGetter;

use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Supla\SuplaServerAware;

class HumidityChannelStateGetter implements SingleChannelStateGetter {
    use SuplaServerAware;

    public function getState(IODeviceChannel $channel): array {
        $value = $this->suplaServer->getHumidityValue($channel);
        if ($value !== false) {
            return ['humidity' => $value];
        } else {
            return [];
        }
    }

    public function supportedFunctions(): array {
        return [
            ChannelFunction::HUMIDITY(),
            ChannelFunction::HUMIDITYANDTEMPERATURE(),
        ];
    }
}
