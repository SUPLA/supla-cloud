<?php

namespace App\Model;

final class TrustedPeerDestination {
    public function __construct(private readonly string $origin) {
    }

    public function getOrigin(): string {
        return $this->origin;
    }
}
