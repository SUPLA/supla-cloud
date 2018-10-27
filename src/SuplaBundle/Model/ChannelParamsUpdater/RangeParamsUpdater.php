<?php
namespace SuplaBundle\Model\ChannelParamsUpdater;

use Assert\Assertion;
use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelType;

abstract class RangeParamsUpdater implements SingleChannelParamsUpdater {
    /** @var ChannelFunction[] */
    private $supportedFunctions;
    /** @var ChannelType[] */
    private $onlyTypes;
    /** @var int */
    private $min;
    /** @var int */
    private $max;
    /** @var int */
    private $paramNo;

    /**
     * RangeParamsUpdater constructor.
     * @param ChannelFunction|ChannelFunction[] $supportedFunctions
     * @param int $min
     * @param int $max
     * @param int $paramNo
     * @param null|ChannelType|ChannelType[] $onlyTypes
     */
    public function __construct($supportedFunctions, int $min, int $max, int $paramNo = 1, $onlyTypes = null) {
        Assertion::inArray($paramNo, [1, 2, 3]);
        $this->supportedFunctions = is_array($supportedFunctions) ? $supportedFunctions : [$supportedFunctions];
        $this->min = $min;
        $this->max = $max;
        $this->paramNo = $paramNo;
        $this->onlyTypes = $onlyTypes !== null ? (is_array($onlyTypes) ? $onlyTypes : [$onlyTypes]) : null;
    }

    public function updateChannelParams(IODeviceChannel $channel, IODeviceChannel $updatedChannel) {
        $newValue = $updatedChannel->getParam($this->paramNo);
        $newValue = max($this->min, $newValue);
        $newValue = min($this->max, $newValue);
        $channel->setParam($this->paramNo, $newValue);
    }

    public function supports(IODeviceChannel $channel): bool {

        return in_array($channel->getFunction(), $this->supportedFunctions)
            && ($this->onlyTypes === null || in_array($channel->getType(), $this->onlyTypes));
    }
}
