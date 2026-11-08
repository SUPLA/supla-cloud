<?php

namespace App\Supla;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="CalcfgActionResult", type="object", required={"status", "id", "queuedAt"},
 *     description="Request-scoped acceptance, not confirmation of device-side execution. ACCEPTED means immediate dispatch was accepted. QUEUED means work was queued or replaced earlier queued work. WAITING_FOR_RESULT means an earlier operation awaits a result and the new payload was not submitted.",
 *     @OA\Property(property="status", type="string", enum={"ACCEPTED", "QUEUED", "WAITING_FOR_RESULT"}),
 *     @OA\Property(property="id", type="integer", description="Command-specific target ID."),
 *     @OA\Property(property="queuedAt", type="integer", nullable=true, description="Unix seconds; null for ACCEPTED, or the earlier enqueue time for WAITING_FOR_RESULT.")
 * )
 */
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
