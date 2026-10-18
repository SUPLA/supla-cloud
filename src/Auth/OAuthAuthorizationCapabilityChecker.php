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
            // Network outage, timeout, or a rejected peer redirect: the
            // target's capability is unknown. AD discovery happens before
            // this method because its input must already be a trusted peer;
            // the caller maps AD discovery failures to target_unavailable.
            return OAuthAuthorizationCapability::targetUnavailable();
        }
        if (!is_array($info)) {
            // The target answered, but server-info itself failed (e.g. 5xx):
            // transient, do not label a broken target as unsupported.
            return OAuthAuthorizationCapability::targetUnavailable();
        }
        if (!array_key_exists('oauthAuthorizationApiVersion', $info)) {
            // A successful server-info response without this field identifies
            // a pre-capability target, independent of cloudVersion.
            return OAuthAuthorizationCapability::unsupported();
        }
        $version = $info['oauthAuthorizationApiVersion'];
        if (!is_int($version)) {
            // A present but malformed capability cannot safely establish
            // either support or permanent absence; treat it as unknown.
            return OAuthAuthorizationCapability::targetUnavailable();
        }
        if ($version === OAuthAuthorizationApi::VERSION) {
            return OAuthAuthorizationCapability::supported(OAuthAuthorizationApi::VERSION);
        }
        // A valid integer version other than this contract version is
        // permanent incompatibility for this request contract.
        return OAuthAuthorizationCapability::unsupported();
    }
}
