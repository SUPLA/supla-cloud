<?php

namespace App\Model;

/**
 * The single broker boundary for turning a trusted-peer record into an
 * outbound destination. Future authorization forwarding must use this class
 * rather than accepting a URL from its operation payload.
 */
final class TrustedPeerDestinationSelector {
    public function select(TargetSuplaCloud $target): TrustedPeerDestination {
        if (!$target->isTrustedPeer()) {
            throw new \InvalidArgumentException('The target Cloud is not a trusted peer.');
        }
        return new TrustedPeerDestination($target->getCanonicalOrigin());
    }
}
