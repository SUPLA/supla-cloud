<?php
namespace SuplaApiBundle\Model\ChannelStateGetter;

use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;

interface SingleChannelStateGetter {
    public function getState(IODeviceChannel $channel): array;

    /** @return ChannelFunction[] */
    public function supportedFunctions(): array;
}
