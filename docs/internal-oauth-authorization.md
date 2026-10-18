# Internal target OAuth authorization (Release A, issue 01)

This is a private broker-to-target operation, **not a browser API**. Release A
keeps the existing OAuth login, Twig consent forms, sessions and token endpoint
active. This change does not activate a new UI or advertise capability support.

## Transport contract

`POST /api/internal/oauth/authorize`, over the configured secure channel, with
the existing `SUPLA-Broker-Token` header. `BrokerRequestSecurityVoter` verifies
the token against Autodiscover's broker identity. A user session or WebApp
bearer token alone is not sufficient. The broker token never authenticates the
end user.

Body fields (JSON, using the existing API body decoder, or form data):

| Field | Meaning |
| --- | --- |
| `client_id` | Public identifier of an already registered local `ApiClient` |
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
uses 429 (`locked`). Protocol errors use FOS's structured `error` field, never
exception descriptions. An error callback is included only after the local
client and exact callback have been verified. Internal storage failure uses
500 (`server_error`) without database or exception details. An exception while
verifying broker identity with AD uses 503 (`target_unavailable`) and never
continues authorization.

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

This issue does not resolve Public AD applications, create/synchronize mapped
clients, forward to another target, accept bearer evidence for prior consent,
or advertise `oauthAuthorizationApiVersion`. Those are later Release A tickets.
Issue 06 must preserve `code_verifier` while forwarding token requests; this
local change validates it at the owning target and does not add forwarding.

The ORM additions are nullable columns on `supla_oauth_auth_codes`:
`code_challenge` (string length 128) and `code_challenge_method` (string length
20). The project owner generates/applies the backwards-compatible migration;
this change intentionally includes no migration file.

No outbound target request was added. Later forwarding must use the trusted
peer forwarder boundary delivered by issue 07, not a request-supplied address.
Broker identity verification continues to depend on the existing AD verifier;
an unavailable/negative verification fails closed. Capability negotiation and
cross-instance error mapping belong to their dependent tickets.

MQTT notification retains the existing post-commit external side effect: this
is not a transactional outbox or an exactly-once distributed delivery guarantee.
No schema changes or migrations are needed by issue 01.
