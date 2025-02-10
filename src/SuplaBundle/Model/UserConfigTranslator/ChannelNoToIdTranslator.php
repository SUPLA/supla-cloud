<?php

namespace SuplaBundle\Model\UserConfigTranslator;

use Assert\Assertion;
use SuplaBundle\Entity\Main\IODeviceChannel;

trait ChannelNoToIdTranslator {
    protected function channelNoToChannel(IODeviceChannel $channel, int $channelNo): ?IODeviceChannel {
        return $channel->getIoDevice()
            ->getChannels()
            ->filter(fn(IODeviceChannel $ch) => $ch->getChannelNumber() === $channelNo)
            ->first() ?: null;
    }

    protected function channelIdToChannelFromDevice(IODeviceChannel $channel, int $channelId): IODeviceChannel {
        $channelWithId = $channel->getIoDevice()
            ->getChannels()
            ->filter(fn(IODeviceChannel $ch) => $ch->getId() === $channelId)
            ->first();
        Assertion::isObject($channelWithId, 'Invalid channel ID given: ' . $channelId);
        return $channelWithId;
    }
}
