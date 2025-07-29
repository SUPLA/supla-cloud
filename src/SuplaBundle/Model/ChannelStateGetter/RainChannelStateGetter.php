<?php
namespace SuplaBundle\Model\ChannelStateGetter;

use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;

class RainChannelStateGetter extends DoubleChannelStateGetter {
    public function getState(IODeviceChannel $channel): array {
        $state = parent::getState($channel);
        if (isset($state['value'])) {
            $state['value'] = $state['value'] * 1000;
        }
        return $state;
    }

    public function supportedFunctions(): array {
        return [ChannelFunction::RAINSENSOR()];
    }
}
