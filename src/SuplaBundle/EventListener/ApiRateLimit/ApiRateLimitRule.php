<?php

namespace SuplaBundle\EventListener\ApiRateLimit;

class ApiRateLimitRule {
    private $period;
    private $limit;

    public function __construct(string $rule) {
        $apiRateLimit = explode('/', $rule);
        $this->limit = intval($apiRateLimit[0]);
        $this->period = intval($apiRateLimit[1] ?? 0);
    }

    public function getPeriod(): int {
        return $this->period;
    }

    public function getLimit(): int {
        return $this->limit;
    }

    public function isValid(): bool {
        return $this->period > 0 && $this->limit > 0;
    }

    public function toString() {
        return "$this->limit/$this->period";
    }

    public function __toString() {
        return $this->toString();
    }

    public function toArray() {
        return [
            'limit' => $this->limit,
            'period' => $this->period,
        ];
    }
}
