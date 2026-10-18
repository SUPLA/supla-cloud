<?php

namespace App\Auth;

/**
 * Result of checking whether a Target Cloud supports the internal OAuth
 * authorization contract (see docs/internal-oauth-authorization.md).
 *
 * Status semantics for callers (e.g. the Release A broker authorization
 * path):
 * - SUPPORTED: the target implements the advertised contract version.
 * - UNSUPPORTED (`unsupported_authorization_api`): the target is reachable
 *   and answered, but does not implement the contract. This is a PERMANENT
 *   condition for the lifetime of the target build: do not retry, fail the
 *   authorization with an update-required result.
 * - TARGET_UNAVAILABLE (`target_unavailable`): the check could not be
 *   completed (network/AD outage, timeout, 5xx). This is TRANSIENT: the
 *   target's capability is unknown, so it must never be labeled
 *   unsupported. Retry with modest backoff and a bounded number of
 *   attempts; do not probe in a tight loop.
 */
final class OAuthAuthorizationCapability {
    public const SUPPORTED = 'supported';
    public const UNSUPPORTED = 'unsupported_authorization_api';
    public const TARGET_UNAVAILABLE = 'target_unavailable';

    private function __construct(
        private readonly string $status,
        private readonly ?int $apiVersion
    ) {
    }

    public static function supported(int $apiVersion): self {
        return new self(self::SUPPORTED, $apiVersion);
    }

    public static function unsupported(): self {
        return new self(self::UNSUPPORTED, null);
    }

    public static function targetUnavailable(): self {
        return new self(self::TARGET_UNAVAILABLE, null);
    }

    public function isSupported(): bool {
        return $this->status === self::SUPPORTED;
    }

    public function getStatus(): string {
        return $this->status;
    }

    public function getApiVersion(): ?int {
        return $this->apiVersion;
    }
}
