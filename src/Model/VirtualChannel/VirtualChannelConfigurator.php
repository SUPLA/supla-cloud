<?php

namespace App\Model\VirtualChannel;

use App\Entity\Main\IODeviceChannel;
use App\Enums\VirtualChannelType;

interface VirtualChannelConfigurator {
    public function configureChannel(IODeviceChannel $channel, array $config): IODeviceChannel;

    public function supports(VirtualChannelType $type): bool;
}
