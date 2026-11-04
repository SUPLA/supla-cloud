<?php

namespace App\Supla;

final class CalcfgActionResult {
    public const ACCEPTED = 'ACCEPTED';
    public const QUEUED = 'QUEUED';
    public const WAITING_FOR_RESULT = 'WAITING_FOR_RESULT';
    public const REJECTED = 'REJECTED';

    public function __construct(
        public readonly string $status,
        public readonly ?int $id,
        public readonly ?int $queuedAt,
    ) {
    }

    public function isAccepted(): bool {
        return in_array($this->status, [self::ACCEPTED, self::QUEUED, self::WAITING_FOR_RESULT], true);
    }
}
