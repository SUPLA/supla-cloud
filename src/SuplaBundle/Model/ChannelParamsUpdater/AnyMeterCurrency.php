<?php
namespace SuplaBundle\Model\ChannelParamsUpdater;

use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Entity\IODeviceChannel;

class AnyMeterCurrency implements SingleChannelParamsUpdater {

    public function updateChannelParams(IODeviceChannel $channel, IODeviceChannel $updatedChannel) {
        if (!$updatedChannel->getTextParam1()
            || preg_match('/^[A-Z]{3}$/', $updatedChannel->getTextParam1())) {
            $channel->setTextParam1($updatedChannel->getTextParam1());
        }

        if (strlen($updatedChannel->getTextParam2() <= 4)) {
            $channel->setTextParam2($updatedChannel->getTextParam2());
        }
    }

    public function supports(IODeviceChannel $channel): bool {
        return in_array($channel->getFunction(), [ChannelFunction::ELECTRICITYMETER(),
            ChannelFunction::GASMETER(),
            ChannelFunction::WATERMETER()]);
    }
}
