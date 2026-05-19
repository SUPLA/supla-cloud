<?php
namespace App\Model\ChannelStateGetter;

use App\Entity\Main\IODeviceChannel;
use App\Enums\ChannelFlags;
use App\Enums\ChannelFunction;
use App\Supla\SuplaServerAware;

class ExtendedChannelStateGetter implements SingleChannelStateGetter {
    use SuplaServerAware;

    public function getState(IODeviceChannel $channel): array {
        if (ChannelFlags::HAS_EXTENDED_CHANNEL_STATE()->isOn($channel->getFlags())) {
            return ['extendedState' => $channel->getLastKnownChannelState()];
        } else {
            return [];
        }
    }

    public function supportedFunctions(): array {
        return ChannelFunction::values();
    }
}
