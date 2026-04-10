<?php

namespace SuplaBundle\EventListener\ApiRateLimit;

class ApiRateLimitRules {
    public function __construct(private string $globalRule, private string $defaultUserRule) {
    }

    public function getGlobalRule(): ApiRateLimitRule {
        return new ApiRateLimitRule($this->globalRule);
    }

    public function getDefaultUserRule(): ApiRateLimitRule {
        return new ApiRateLimitRule($this->defaultUserRule);
    }
}
