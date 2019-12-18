<?php

namespace SuplaBundle\Model\ChannelParamsUpdater\ChannelParamsConfig;

use SuplaBundle\Entity\IODeviceChannel;

interface ChannelParamTranslator {
    public function getConfigFromParams(IODeviceChannel $channel): array;

    public function setParamsFromConfig(array $config, IODeviceChannel $channel);

    public function supports(IODeviceChannel $channel): bool;
}
