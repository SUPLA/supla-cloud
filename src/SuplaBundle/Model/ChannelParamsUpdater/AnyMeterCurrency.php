<?php
namespace SuplaBundle\Model\ChannelParamsUpdater;

use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Entity\Main\IODeviceChannel;

class AnyMeterCurrency implements SingleChannelParamsUpdater {

    public function updateChannelParams(IODeviceChannel $channel, IODeviceChannel $updatedChannel) {
        if (!$updatedChannel->getTextParam1()
            || preg_match('/^[A-Z]{3}$/', $updatedChannel->getTextParam1())) {
            $channel->setTextParam1($updatedChannel->getTextParam1());
        }
    }

    public function supports(IODeviceChannel $channel): bool {
        return in_array($channel->getFunction(), [ChannelFunction::ELECTRICITYMETER(),
            ChannelFunction::GASMETER(),
            ChannelFunction::WATERMETER(),
            ChannelFunction::HEATMETER()]);
    }
}
