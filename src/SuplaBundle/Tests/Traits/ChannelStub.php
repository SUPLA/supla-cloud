<?php

namespace SuplaBundle\Tests\Traits;

use SuplaBundle\Entity\EntityUtils;
use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelType;

final class ChannelStub extends IODeviceChannel {
    public function __construct(?ChannelType $type) {
        parent::__construct();
        $this->type($type ?: ChannelType::THERMOMETER());
        $this->flags(0b1111111111111111111111111);
        $this->funcList(0b1111111111111111111111111);
    }

    public static function create(?ChannelType $type = null): self {
        return new self($type);
    }

    public function type(ChannelType $channelType): self {
        EntityUtils::setField($this, 'type', $channelType->getId());
        if ($functions = ChannelFunction::forChannel($this)) {
            $this->setFunction($functions[0]);
        }
        return $this;
    }

    public function setFunction($function): self {
        parent::setFunction($function);
        return $this;
    }

    public function flags(int $flags): self {
        EntityUtils::setField($this, 'flags', $flags);
        return $this;
    }

    private function funcList(int $funcList) {
        EntityUtils::setField($this, 'funcList', $funcList);
        return $this;
    }
}
