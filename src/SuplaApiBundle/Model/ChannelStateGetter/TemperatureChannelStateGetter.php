<?php
namespace SuplaApiBundle\Model\ChannelStateGetter;

use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Supla\SuplaServerAware;

class TemperatureChannelStateGetter implements SingleChannelStateGetter {
    use SuplaServerAware;

    public function getState(IODeviceChannel $channel): array {
        $value = $this->suplaServer->getTemperatureValue($channel->getUser()->getId(), $channel->getIoDevice()->getId(), $channel->getId());
        if ($value !== false) {
            return ['temperature' => $value];
        } else {
            return [];
        }
    }

    public function supportedFunctions(): array {
        return [
            ChannelFunction::THERMOMETER(),
            ChannelFunction::HUMIDITYANDTEMPERATURE(),
        ];
    }
}
