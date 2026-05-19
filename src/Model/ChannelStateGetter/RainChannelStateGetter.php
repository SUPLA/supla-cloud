<?php
namespace App\Model\ChannelStateGetter;

use App\Entity\Main\IODeviceChannel;
use App\Enums\ChannelFunction;

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
