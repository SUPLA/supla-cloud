<?php
namespace SuplaBundle\Model\ChannelStateGetter;

use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Supla\SuplaServerAware;

class DistanceChannelStateGetter implements SingleChannelStateGetter {
    use SuplaServerAware;

    public function getState(IODeviceChannel $channel): array {
        $value = $this->suplaServer->getDoubleValue($channel);
        if ($value !== false) {
            $key = $channel->getFunction() == ChannelFunction::DISTANCESENSOR() ? 'distance' : 'depth';
            return [$key => $value];
        } else {
            return [];
        }
    }

    public function supportedFunctions(): array {
        return [
            ChannelFunction::DISTANCESENSOR(),
            ChannelFunction::DEPTHSENSOR(),
        ];
    }
}
