<?php

namespace SuplaBundle\Model\ChannelParamsUpdater;

use SuplaBundle\Entity\IODeviceChannel;

class ChannelParamConfigTranslator {
    public function getConfigFromParams(IODeviceChannel $channel): array {
        return [];
    }

    public function setParamsFromConfig(array $config, IODeviceChannel $channel): void {
    }
}
