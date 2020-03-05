<?php
namespace SuplaBundle\Model\ChannelParamsUpdater;

use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Enums\ChannelType;

class AnyMeterUnit implements SingleChannelParamsUpdater {

    public function updateChannelParams(IODeviceChannel $channel, IODeviceChannel $updatedChannel) {
        if (strlen($updatedChannel->getTextParam2() <= 4)) {
            $channel->setTextParam2($updatedChannel->getTextParam2());
        }
    }

    public function supports(IODeviceChannel $channel): bool {
        return $channel->getType() == ChannelType::IMPULSECOUNTER()
            && in_array($channel->getFunction(), [ChannelFunction::ELECTRICITYMETER(),
            ChannelFunction::GASMETER(),
            ChannelFunction::WATERMETER(),
            ChannelFunction::HEATMETER()]);
    }
}
