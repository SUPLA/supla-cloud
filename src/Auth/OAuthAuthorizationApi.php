<?php

namespace App\Auth;

/**
 * Version of the internal broker-to-target OAuth authorization contract
 * (`POST /api/internal/oauth/authorize`, see docs/internal-oauth-authorization.md).
 *
 * An instance that ships the internal authorization endpoint advertises this
 * exact value as `oauthAuthorizationApiVersion` in `GET /api/server-info`.
 * The value is a compile-time fact about the running build, not a runtime
 * setting: an instance built before the endpoint existed simply does not
 * emit the field. It must not be derived from, or coupled to, the generic
 * `cloudVersion` application version string.
 */
final class OAuthAuthorizationApi {
    public const VERSION = 1;
}
