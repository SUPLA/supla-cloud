<?php

namespace SuplaBundle\Model\UserConfigTranslator;

use SuplaBundle\Entity\Main\IODeviceChannel;

interface ChannelParamTranslator {
    public function getConfigFromParams(IODeviceChannel $channel): array;

    public function setParamsFromConfig(IODeviceChannel $channel, array $config);

    public function supports(IODeviceChannel $channel): bool;
}
