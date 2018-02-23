<?php
namespace SuplaApiBundle\Model\ChannelStateGetter;

use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Supla\SuplaServerAware;

class DistanceChannelStateGetter implements SingleChannelStateGetter {
    use SuplaServerAware;

    public function getState(IODeviceChannel $channel): array {
        $value = $this->suplaServer->getDistanceValue($channel->getUser()->getId(), $channel->getIoDevice()->getId(), $channel->getId());
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
