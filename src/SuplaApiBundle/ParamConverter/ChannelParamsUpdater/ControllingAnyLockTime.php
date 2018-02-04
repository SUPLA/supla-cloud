<?php
namespace SuplaApiBundle\ParamConverter\ChannelParamsUpdater;

use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;

abstract class ControllingAnyLockTime implements SingleChannelParamsUpdater {
    /** @var ChannelFunction */
    private $supportedFunction;
    /** @var int */
    private $min;
    /** @var int */
    private $max;

    public function __construct(ChannelFunction $supportedFunction, int $min, int $max) {
        $this->supportedFunction = $supportedFunction;
        $this->min = $min;
        $this->max = $max;
    }

    public function updateChannelParams(IODeviceChannel $channel, IODeviceChannel $updatedChannel) {
        $newTime = $updatedChannel->getParam1();
        $newTime = max($this->min, $newTime);
        $newTime = min($this->max, $newTime);
        $channel->setParam1($newTime);
    }

    public function supports(IODeviceChannel $channel): bool {
        return $channel->getFunction() == $this->supportedFunction;
    }
}
