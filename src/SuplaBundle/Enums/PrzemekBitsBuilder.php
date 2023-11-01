<?php

namespace SuplaBundle\Enums;

use Assert\Assertion;

class PrzemekBitsBuilder {
    private $value = 0;

    public function add($bit): self {
        $bitValue = $bit instanceof ChannelFunctionBits ? $bit->getValue() : $bit;
        Assertion::integer($bitValue);
        $this->value |= $bitValue;
        return $this;
    }

    public function getValue(): int {
        return $this->value;
    }
}
