<?php

namespace SuplaBundle\Model\ChannelParamsUpdater\ChannelParamsConfig;

use SuplaBundle\Entity\IODeviceChannel;

interface ChannelParamTranslator {
    public function getConfigFromParams(IODeviceChannel $channel): array;

    public function setParamsFromConfig(IODeviceChannel $channel, array $config);

    public function supports(IODeviceChannel $channel): bool;
}
