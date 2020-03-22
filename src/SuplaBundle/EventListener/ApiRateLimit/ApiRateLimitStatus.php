<?php

namespace SuplaBundle\EventListener\ApiRateLimit;

use SuplaBundle\Model\TimeProvider;

class ApiRateLimitStatus {
    private $limit;
    private $remaining;
    private $reset;

    public function __construct(string $status) {
        $status = explode(',', $status);
        $this->limit = intval($status[0]);
        $this->remaining = intval($status[1] ?? 0);
        $this->reset = intval($status[2] ?? 0);
    }

    public function getLimit(): int {
        return $this->limit;
    }

    public function getRemaining(): int {
        return $this->isExceeded() ? 0 : $this->remaining;
    }

    public function getReset(): int {
        return $this->reset;
    }

    public function decrement() {
        --$this->remaining;
    }

    public function isExceeded(): bool {
        return $this->remaining < 0;
    }

    public function toString() {
        return "$this->limit,$this->remaining,$this->reset";
    }

    public function __toString() {
        return $this->toString();
    }

    public static function fromRule(ApiRateLimitRule $rule, TimeProvider $timeProvider): self {
        $limit = $rule->getLimit();
        $remaining = $rule->getLimit();
        $reset = $timeProvider->getTimestamp() + $rule->getPeriod();
        return new self("$limit,$remaining,$reset");
    }
}
