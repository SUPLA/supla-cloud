# Internal target OAuth authorization (Release A, issue 01)

This is a private broker-to-target operation, **not a browser API**. Release A
keeps the existing OAuth login, Twig consent forms, sessions and token endpoint
active. This contract remains separate from the legacy browser flow.

## Transport contract

`POST /api/internal/oauth/authorize`, over the configured secure channel, with
the existing `SUPLA-Broker-Token` header. `BrokerRequestSecurityVoter` verifies
the token against Autodiscover's broker identity. A user session or WebApp
bearer token alone is not sufficient. The broker token never authenticates the
end user.

Body fields (JSON, using the existing API body decoder, or form data):

| Field | Meaning |
| --- | --- |
| `client_id` | Registered local client id or the target-local mapped id for a Public AD application |
| `redirect_uri` | Entire, exact registered callback, without a fragment |
| `response_type` | `code` only |
| `scope` | Nonempty, currently supported SUPLA scopes, separated by spaces |
| `state` | Optional opaque string; returned unchanged, including `"0"`/empty |
| `code_challenge`, `code_challenge_method` | Optional PKCE binding; if used, the method must be `S256` and the challenge a canonical base64url SHA-256 value |
| `decision` | `approve` or `deny` |
| `username`, `password` | Required for approval; target verifies the password |

Fields must be strings. Unknown fields are rejected rather than silently
discarding security evidence. Only authorization parameters are copied into
the FOS request query; passwords never go into that query or a session. A
challenge without a method, a `plain` challenge, any other method, and malformed
challenges are rejected as `invalid_request` once the callback is verified.
PKCE is optional for every client; Autodiscover catalog membership does not
classify an OAuth client or make PKCE mandatory.
Denial requires broker authentication and a valid authorization request, but
does not require user credentials and never changes consent.

Every operation result is JSON with `Cache-Control: no-store`:

```json
{"outcome":"authorized","redirectUrl":"https://registered.example/callback?code=...&state=..."}
```

```json
{"outcome":"denied","redirectUrl":"https://registered.example/callback?error=access_denied&state=..."}
```

```json
{"outcome":"error","errorCode":"invalid_scope","redirectUrl":"https://registered.example/callback?error=invalid_scope&state=..."}
```

There are no additional result fields. Success/denial use HTTP 200. Invalid
requests use 400; invalid broker/user credentials use 401 (`unauthorized_broker`
or `invalid_credentials`); disabled users use 409 (`disabled`); account lockout
uses 429 (`locked`). A concurrent mapped-client insert returns 409
(`client_mapping_retry`) without a callback; retry the same request in a fresh
HTTP request. Protocol errors use FOS's structured `error` field, never
exception descriptions. An error callback is included only after the local
client and exact callback have been verified. Internal storage failure uses
500 (`server_error`) without database or exception details. An exception while
verifying broker identity or resolving a mapped client with AD uses 503
(`target_unavailable`) and never continues authorization.

## Mapped public client resolution (Release A, issue 03)

For an already registered local client, authorization remains local. A target-
local mapped id that is not yet registered is resolved using the existing
Autodiscover reverse mapping, public metadata lookup, lazy `BROKER` client
creation, and `updateTargetCloudCredentials` synchronization. FOS then uses the
new local client id for validation and issuance. Subsequent requests resolve
that canonical local client and refresh its public metadata before checking the
exact callback; a callback absent from current metadata is rejected without a
redirect. Unknown or inconsistent mappings and AD outages fail closed.

The nullable `public_client_id` column has a UNIQUE constraint so concurrent
workers cannot create two target-local representations of the same Public AD
application. The database constraint is the cross-process race arbiter; this
path does not depend on a process-local lock. If another request commits the
canonical row between lookup and insert, the losing request receives
`409 client_mapping_retry`, with no consent or authorization code. Retry the
unchanged request in a new HTTP request/EntityManager: it will resolve the
committed row by `public_client_id`, refresh metadata, and synchronize
credentials before continuing.

### Upgrade prerequisite for the UNIQUE constraint

Historical code may have left duplicate non-NULL `public_client_id` values.
Before applying the generated schema migration, run this read-only preflight
against the target Cloud database:

```sql
SELECT public_client_id,
       COUNT(*) AS client_count,
       GROUP_CONCAT(id ORDER BY id) AS api_client_ids
FROM supla_oauth_clients
WHERE public_client_id IS NOT NULL
GROUP BY public_client_id
HAVING COUNT(*) > 1;
```

The migration may proceed only after the query returns no rows. If it returns
duplicates, stop the upgrade and have the operator investigate each client
against its Autodiscover mapping and associated grants, codes, and tokens.
Choose and apply a reviewed, application-aware remediation, then rerun the
preflight. Do not automatically delete or merge client rows. This repository
contains no generated migration; migration generation and execution remain an
operator-maintained upgrade step.

Approval uses normal password hashing/migration, account-status checks and
authentication audit/lockout even for non-WebApp clients. Legacy `api_N`
compatibility usernames are not accepted on this new operation. Audits remain
on rejected logins, but no authorization grant or code is written for rejected
credentials or invalid authorization requests.

## Pinned FOS compatibility approach

Inspected dependencies: `klapaudius/oauth-server-bundle` **3.1.6** and
`klapaudius/oauth2-php` **1.10.0**.

* oauth2-php exposes `getAuthorizeParams(Request)` as a **protected** validator.
  `SuplaOAuth2::validateAuthorizationRequest()` is a thin public adapter to it.
  The API does not probe validation with a synthetic denial.
* Issuance is still `finishClientAuthorization(true, $user, $request, $scope)`.
  FOS generates and stores the code through its existing auth-code manager.
  The existing `/oauth/v2/token` exchanges it, retaining client/callback binding,
  scope normalization, the SUPLA suffix format and single-use semantics.
* oauth2-php 1.10.0 does not implement PKCE. The local `AuthCode` entity adds
  nullable `codeChallenge` and `codeChallengeMethod` metadata; custom FOS storage
  sets them on the existing code object before its insert. Code exchange
  requires a 43–128 character verifier using the RFC unreserved set and checks
  its S256 digest. A missing, malformed or mismatched verifier is `invalid_grant`;
  a verifier supplied for an unbound code is `invalid_request`. Verification
  follows FOS client and code-lifetime checks. Since pinned FOS callback
  matching accepts path prefixes, PKCE-bound codes additionally require exact
  callback equality with the URI stored on that code. A pessimistic row lock is
  held through FOS token creation and code deletion, so concurrent exchanges
  cannot both succeed. Legacy rows and new non-PKCE codes keep both fields null.
* The legacy FOS consent form posts to a fixed route without the original query.
  A small form-type extension adds unmapped hidden challenge/method fields so the
  authorization request reaches the same FOS-backed issuance hook. It does not
  store PKCE state in the session or create a separate code issuer.
* Pinned FOS matches callback prefixes and sanitizes input. The internal API
  first requires an exact original registered URI; this stricter rule is not
  applied retroactively to the legacy browser controller. The API also limits
  response types to codes, checks the client's permitted grant type using FOS
  storage, and uses `OAuthScope` to reject retired/empty consent scopes before
  any writes. These are narrow contract guards, not a second OAuth validator.
* Pinned `OAuth2RedirectException` drops falsey state. The internal result
  adapter restores an explicitly supplied `"0"` or empty state on FOS error/
  denial URLs. FOS still constructs the rest of the callback, preserving its
  registered query parameters.
* FOS's browser controller emits PRE, then (on accepted/denied forms) POST
  before completion. Those events, their order and session invalidation remain
  untouched. The internal API emits **no synthetic FOS events**. Instead,
  `OAuthAuthorizationGrantService` is shared with the existing listener's POST
  handler for scope union and MQTT enablement/notification.
* The shared service serializes grants on the owning user's database row. On
  the internal path it commits the consent union and FOS completion together;
  an issuance/storage exception rolls back both. MQTT is enabled in that
  transaction and notified after commit, with the explicitly verified user
  rather than a session/security-token subject. Repeated approvals do not
  create duplicate grant rows or repeat MQTT enablement notifications.

## Scope and extension boundaries

The integrated backend supports local password authorization (01), verified
prior-consent reuse (02), mapped-client resolution (03), trusted peer transport
(07), capability negotiation (08), and local PKCE (05). Broker authorization
orchestration and brokered PKCE remain separate Release A tickets; no new
browser flow is activated.

## Brokered PKCE token exchange (Release A, issue 06)

The existing `/oauth/v2/token` broker flow forwards the full incoming request
body to the trusted Target Cloud and overlays only the AD-mapped client ID and
secret. This preserves the original `code_verifier` unchanged in the secure
request body. The verifier is neither appended to the authorization-code
suffix nor put in a URL, and the broker does not log or transform it. The
Target Cloud validates the verifier against the S256 challenge stored with the
individual FOS authorization code; it rejects absent, malformed, incorrect or
downgraded proofs and retains FOS client/callback binding and single-use code
semantics.

The token endpoint rejects `code_verifier` in the query for both GET and POST
with OAuth `invalid_request`, before target exchange or broker forwarding. A
POST body verifier remains supported, as do legacy GET exchanges that omit a
verifier.

The integration fixture exercises target issuance through the authenticated
internal authorization HTTP endpoint, followed by public token exchange at
the broker endpoint, then delivers the forwarded request to a separate target
Symfony kernel over its actual token HTTP endpoint. It covers mapped target
credentials, exact verifier preservation, target-side proof failures, changed
callback/client mismatch, one-time exchange, and non-PKCE authorization-code
plus refresh-token compatibility. The authorization-code/token suffix format
is unchanged.

The ORM additions are nullable columns on `supla_oauth_auth_codes`:
`code_challenge` (string length 128) and `code_challenge_method` (string length
20). The project owner generates/applies the backwards-compatible migration;
this change intentionally includes no migration file.

No outbound target request was added. Later forwarding must use the trusted
peer forwarder boundary delivered by issue 07, not a request-supplied address.
Broker identity verification continues to depend on the existing AD verifier;
an unavailable/negative verification fails closed. Capability negotiation is
described below; authorization forwarding belongs to issue 04.

## Broker password authorization operation (Release A, issue 04)

`POST /api/oauth/authorize` is a dormant backend operation; it does not change
the legacy OAuth browser login/form/session routes. It accepts the original
authorization parameters plus `decision=approve`, `username`, and either
`password` or body-only `target_token` evidence (never both). Username is
retained for AD owner discovery and binds the verified identity when token
evidence is used. The broker resolves the account owner through Autodiscover, resolves the
public client's target-local mapped id through Autodiscover, and verifies
`oauthAuthorizationApiVersion: 1` before forwarding. The request is sent only
through `TargetSuplaCloudRequestForwarder` after
`TrustedPeerDestinationSelector` accepts the AD-derived trusted peer. Browser
parameters, token suffixes, and caller-supplied trust flags cannot select a
destination.

The target verifies the password or bearer evidence and owns grant mutation
and FOS code issuance. The broker returns the target's structured result unchanged and
never redirects the browser. A missing capability returns
`unsupported_authorization_api`; AD, capability, or transport failures return
`target_unavailable`. The broker does not retry an authorization call. In
particular, `409 client_mapping_retry` is returned for a fresh bounded retry
by the caller; do not replay side effects within the same request.

MQTT notification retains the existing post-commit external side effect: this
is not a transactional outbox or an exactly-once distributed delivery guarantee.
Issue 01 introduced no schema changes. Issue 03 adds the mapped-public-client
UNIQUE constraint described above; its migration is generated and applied
separately by the operator.

## Release A capability advertisement (issue 08)

An instance that ships this endpoint advertises the integer
`oauthAuthorizationApiVersion: 1` in `GET /api/server-info`. It is included on
all server-info API versions, including the legacy wrapped response shape, and
does not reveal credentials, users, grants, or authorization outcomes. The
field reflects the deployed internal authorization endpoint above; it is not
derived from `cloudVersion`. An older instance keeps its existing browser OAuth
flow and simply omits this field.

Broker-side code uses
`App\Auth\OAuthAuthorizationCapabilityChecker::check(TargetSuplaCloud $target): OAuthAuthorizationCapability`
after the target has already been resolved by Autodiscover or an equivalent
trusted-peer registry. The checker performs only the public, unauthenticated
`server-info` read through `TargetSuplaCloudRequestForwarder`, which enforces
the issue-07 trusted-peer destination selector, DNS/IP validation,
no-redirect policy, and bounded timeouts. It does not accept a URL/address and
rejects a non-trusted target before making a request. The server-info endpoint
is intentionally public and read-only; sensitive authorization remains on the
broker-authenticated internal POST endpoint above.

The result status has stable machine-readable semantics for broker callers:

| `getStatus()` | Meaning | Retry guidance |
| --- | --- | --- |
| `supported` | A valid integer field equals `1`; `getApiVersion()` is `1`. | No probe needed again for this operation. |
| `unsupported_authorization_api` | A successful server-info response omitted the field, or advertised a different integer contract version. | Permanent for this target build; return update-required and do not retry in a loop. |
| `target_unavailable` | Network/timeout/redirect rejection, non-200 server-info (including 5xx), malformed response value, or capability otherwise unknown. | Transient; retry only with a bounded attempt count and modest backoff. Do not issue repetitive probes. |

Autodiscover resolution happens before the checker by design. If AD is
unavailable or cannot provide a trusted Target Cloud, the caller must return
`target_unavailable`; it must not call the checker with an untrusted or
client-supplied destination. The checker itself returns `target_unavailable`
for outbound transient failures, never `unsupported_authorization_api`.

### Pre-registration compatibility probe (issue 07 exception)

The existing `POST /api/register-target-cloud` workflow has a separate,
narrowly scoped exception: before Autodiscover registers a private Cloud as a
peer, it reads that submitted origin's public `server-info` to preserve the
existing availability, HTTPS, and minimum-version checks. This probe accepts
only a canonical HTTPS origin and uses the same pinned outbound transport as
trusted-peer reads (prohibited-IP rejection, DNS resolution pinned to the
connection, TLS certificate/host verification, no redirects, and bounded
connect/overall timeouts). The probe does not mark the origin trusted and does
not call the generic broker-token forwarder or attach a stored broker token.

This exception is exclusively for pre-registration `server-info`. OAuth
authorization, authorization-code exchange, refresh-token exchange, and all
other credential-bearing or private operations continue to require an
Autodiscover/private-cloud trusted-peer record and the strict peer selector.

Trusted-peer DNS bounding launches the runtime's PHP CLI executable from
`PHP_BINDIR` (`php.exe` on Windows) through Symfony Process, which requires
`proc_open`. The supported `supla-docker` deployment runs the Cloud image's
console as `php bin/console` (see the
[supla-docker compose definition](https://github.com/SUPLA/supla-docker/blob/master/docker-compose.yml)
and its `supla.sh` console command); the runtime therefore needs an available
PHP CLI. If it is missing or unusable, trusted-peer forwarding fails closed
before a network connection is made. Install/enable the PHP CLI and
`proc_open` in the Cloud runtime rather than configuring an alternate
executable path.
