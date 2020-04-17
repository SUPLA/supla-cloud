<?php

namespace SuplaBundle\EventListener\ApiRateLimit;

class GlobalApiRateLimit extends ApiRateLimitRule {
    public function __construct(string $rule) {
        parent::__construct($rule);
    }
}
