<?php

namespace SuplaBundle\Tests\Traits;

use SuplaBundle\Entity\EntityUtils;
use SuplaBundle\Entity\IODeviceChannel;
use SuplaBundle\Enums\ChannelFunction;
use SuplaBundle\Enums\ChannelType;

final class ChannelStub extends IODeviceChannel {
    public function __construct($typeOrFunction) {
        parent::__construct();
        if ($typeOrFunction instanceof ChannelType) {
            $this->type($typeOrFunction);
        } elseif ($typeOrFunction instanceof ChannelFunction) {
            $this->setFunction($typeOrFunction);
        } else {
            $this->setFunction(ChannelFunction::THERMOMETER());
        }
        $this->id(1)
            ->flags(0b1111111111111111111111111)
            ->funcList(0b1111111111111111111111111);
    }

    public static function create($typeOrFunction = null): self {
        return new self($typeOrFunction);
    }

    public function type(ChannelType $channelType): self {
        EntityUtils::setField($this, 'type', $channelType->getId());
        if (!$this->typeMatchesFunction() && ($functions = ChannelFunction::forChannel($this))) {
            $this->setFunction($functions[0]);
        }
        return $this;
    }

    public function setFunction($function): self {
        parent::setFunction($function);
        $this->ensureTypeMatchesFunction();
        return $this;
    }

    private function typeMatchesFunction(): bool {
        return in_array($this->getFunction()->getId(), EntityUtils::mapToIds(ChannelFunction::forChannel($this)));
    }

    private function ensureTypeMatchesFunction() {
        if (!$this->typeMatchesFunction()) {
            $possibleTypes = ChannelType::values();
            $possibleType = current($possibleTypes);
            while (!$this->typeMatchesFunction() && $possibleType) {
                EntityUtils::setField($this, 'type', $possibleType->getId());
                $possibleType = next($possibleTypes);
            }
            if (!$this->typeMatchesFunction()) {
                $this->type(ChannelType::safeInstance(-1));
            }
        }
    }

    public function id(int $id): self {
        EntityUtils::setField($this, 'id', $id);
        return $this;
    }

    public function flags(int $flags): self {
        EntityUtils::setField($this, 'flags', $flags);
        return $this;
    }

    public function config(array $config): self {
        $this->setConfig($config);
        return $this;
    }

    public function serverConfig(array $config): self {
        EntityUtils::setField($this, 'serverConfig', json_encode($config));
        return $this;
    }

    private function funcList(int $funcList) {
        EntityUtils::setField($this, 'funcList', $funcList);
        return $this;
    }
}
