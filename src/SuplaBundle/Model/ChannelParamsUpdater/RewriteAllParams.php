<?php
namespace SuplaBundle\Model\ChannelParamsUpdater;

use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;

class RewriteAllParams implements SingleChannelParamsUpdater {
    public function updateChannelParams(IODeviceChannel $channel, IODeviceChannel $updatedChannel) {
        $channel->setParam1($updatedChannel->getParam1());
        $channel->setParam2($updatedChannel->getParam2());
        $channel->setParam3($updatedChannel->getParam3());
        $channel->setTextParam1($updatedChannel->getTextParam1());
        $channel->setTextParam2($updatedChannel->getTextParam2());
        $channel->setTextParam3($updatedChannel->getTextParam3());
    }

    public function supports(IODeviceChannel $channel): bool {
        return in_array($channel->getFunction()->getId(), [
            ChannelFunction::GENERAL_PURPOSE_MEASUREMENT,
        ]);
    }
}
