<?php
namespace SuplaApiBundle\ParamConverter\ChannelParamsUpdater;

use Assert\Assertion;
use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;

abstract class RangeParamsUpdater implements SingleChannelParamsUpdater {
    /** @var ChannelFunction */
    private $supportedFunction;
    /** @var int */
    private $min;
    /** @var int */
    private $max;
    /** @var int */
    private $paramNo;

    public function __construct(ChannelFunction $supportedFunction, int $min, int $max, int $paramNo = 1) {
        Assertion::inArray($paramNo, [1, 2, 3]);
        $this->supportedFunction = $supportedFunction;
        $this->min = $min;
        $this->max = $max;
        $this->paramNo = $paramNo;
    }

    public function updateChannelParams(IODeviceChannel $channel, IODeviceChannel $updatedChannel) {
        $getter = "getParam$this->paramNo";
        $setter = "setParam$this->paramNo";
        $newValue = $updatedChannel->{$getter}();
        $newValue = max($this->min, $newValue);
        $newValue = min($this->max, $newValue);
        $channel->{$setter}($newValue);
    }

    public function supports(IODeviceChannel $channel): bool {
        return $channel->getFunction() == $this->supportedFunction;
    }
}
