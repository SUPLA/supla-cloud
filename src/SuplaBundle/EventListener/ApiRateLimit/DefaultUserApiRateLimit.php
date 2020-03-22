<?php

namespace SuplaBundle\EventListener\ApiRateLimit;

class DefaultUserApiRateLimit extends ApiRateLimitRule {
    public function __construct(string $rule) {
        parent::__construct($rule);
    }
}
