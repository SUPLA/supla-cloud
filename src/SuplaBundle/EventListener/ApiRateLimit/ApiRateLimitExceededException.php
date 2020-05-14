<?php

namespace SuplaBundle\EventListener\ApiRateLimit;

use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

class ApiRateLimitExceededException extends TooManyRequestsHttpException {
    private $reason;

    public function __construct(int $retryAfter, string $reason) {
        parent::__construct($retryAfter, $this->getUserMessage($reason), null, $this->getReasonCode($reason));
        $this->reason = $reason;
    }

    public function getReason(): string {
        return $this->reason;
    }

    private function getUserMessage(string $reason): string {
        return [
                'user' => 'You have reached your API rate limit. Slow down.',
            ][$reason] ?? 'API cannot respond right now. Wait a while before subsequent request.';
    }

    private function getReasonCode(string $reason): int {
        return $reason === 'user' ? 1 : 0;
    }
}
