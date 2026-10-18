<?php

namespace App\Auth;

use App\Exception\ApiException;
use App\Model\TargetSuplaCloud;
use App\Model\TargetSuplaCloudRequestForwarder;

/**
 * Checks whether an already-trusted Target Cloud supports the internal
 * OAuth authorization contract (OAuthAuthorizationApi::VERSION).
 *
 * The only outbound request is the target's public, unauthenticated
 * server-info, sent through the trusted-peer forwarder boundary
 * (TrustedPeerDestinationSelector, issue 07). A target that was not
 * resolved as a trusted peer by Autodiscover or an equivalent registry
 * rejects the check with InvalidArgumentException before any request is
 * made, so the probe can never be pointed at a client-controlled address.
 */
final class OAuthAuthorizationCapabilityChecker {
    public function __construct(
        private readonly TargetSuplaCloudRequestForwarder $requestForwarder
    ) {
    }

    public function check(TargetSuplaCloud $target): OAuthAuthorizationCapability {
        try {
            $info = $this->requestForwarder->getInfo($target);
        } catch (ApiException $e) {
            // Network/AD outage, timeout, or a rejected peer redirect:
            // transient, the target's capability is unknown.
            return OAuthAuthorizationCapability::targetUnavailable();
        }
        if (!is_array($info)) {
            // The target answered, but server-info itself failed (e.g. 5xx):
            // transient, do not label a broken target as unsupported.
            return OAuthAuthorizationCapability::targetUnavailable();
        }
        if (($info['oauthAuthorizationApiVersion'] ?? null) === OAuthAuthorizationApi::VERSION) {
            return OAuthAuthorizationCapability::supported(OAuthAuthorizationApi::VERSION);
        }
        // The target answered server-info without the capability field: a
        // pre-Release-A instance, a permanent missing-capability condition.
        return OAuthAuthorizationCapability::unsupported();
    }
}
