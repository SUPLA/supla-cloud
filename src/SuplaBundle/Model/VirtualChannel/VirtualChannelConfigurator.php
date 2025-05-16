<?php

namespace SuplaBundle\Model\VirtualChannel;

use SuplaBundle\Entity\Main\IODeviceChannel;
use SuplaBundle\Enums\VirtualChannelType;

interface VirtualChannelConfigurator {
    public function configureChannel(IODeviceChannel $channel, array $config): IODeviceChannel;

    public function supports(VirtualChannelType $type): bool;
}
