<?php

namespace SuplaBundle\Migrations\Factory;

use SuplaBundle\Model\Dependencies\ChannelDependencies;

interface ChannelDependenciesAware {
    public function setChannelDependencies(ChannelDependencies $channelDependencies): void;
}
