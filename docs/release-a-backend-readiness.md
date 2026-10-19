# Release A backend readiness checkpoint

**Status:** Release A authorization readiness is confirmed by the passing legacy and new server-side authorization integration checks below. Per the user's acceptance decision, fleet inventory/contact is not required for this code-readiness checkpoint. This does not claim that Release A has shipped or been deployed.

**Code boundary:** `stateless-oauth-release-a` at `c62f7ca48eef89bc0ee6892984a9c5411b0c96b4` (Release A issues 01–08). This is an untagged candidate boundary, not a published or deployed release. The issue-09 checkpoint documentation is separate from that executable-code boundary.

## What Release A provides

- The existing OAuth browser authorization experience remains active: Symfony sessions, login/consent forms rendered by Twig, the existing routes, and existing code/token formats are retained. Release A does not activate the Vue authorization flow or disable sessions.
- Target Cloud-owned authorization and Public AD application-to-Mapped client support are available through the internal authorization API. Broker Cloud calls require the Broker Cloud authentication contract and independently trusted Target Cloud selection.
- Authorization codes support optional S256 PKCE locally and across Broker Cloud token exchange. Existing non-PKCE clients and refresh-token behavior remain supported.
- `GET /api/server-info` reports `oauthAuthorizationApiVersion: 1` when the internal authorization API is available. Capability, not `cloudVersion`, is the readiness signal for the new contract.
- Legacy Broker Cloud token forwarding and the registration-only private-cloud probe use the hardened destination policy. The registration probe does not grant peer trust or permit OAuth forwarding.

The internal API contract, capability behavior, and PHP CLI/`proc_open` runtime requirement are described in [internal OAuth authorization](internal-oauth-authorization.md).

## Schema and rollout prerequisites

Release A adds nullable PKCE challenge metadata to authorization codes and a uniqueness constraint for mapped `public_client_id` values. These changes preserve existing code rows and legacy requests, but deployment requires a migration generated and reviewed by the operator; this repository intentionally contains no generated migration. Before applying it:

1. Back up the database and inspect existing `public_client_id` values for duplicates that would prevent the unique constraint.
2. Generate and review the migration from the Release A Doctrine entities. Confirm the PKCE columns are nullable and the uniqueness constraint is compatible with the installation's data.
3. Apply the migration before deploying code that depends on the new schema. Keep the resulting schema when rolling application code back to the prior Release A-compatible build; do not remove data/columns as part of an application rollback.
4. Confirm the runtime has PHP CLI at `PHP_BINDIR/php` (or `php.exe`) and `proc_open` enabled. Trusted-peer DNS resolution fails closed when the CLI is unavailable.

No migration has been generated or applied as part of this checkpoint.

## Backend verification performed

At the code boundary above, these integration classes passed against an isolated `supla_agent09oauth_test` database, run as two sequential groups:

| Coverage | Integration classes | Result |
| --- | --- | --- |
| Legacy OAuth/session authorization and Broker Cloud forwarding | `OAuthAuthenticationIntegrationTest`, `OAuthBrokerAuthorizationIntegrationTest` | 63 tests, 315 assertions; 1 existing skip |
| Target Cloud-owned and Broker Cloud-mediated future authorization, PKCE, capability/mixed-version behavior | `InternalOAuthAuthorizationIntegrationTest`, `OauthAuthorizationCapabilityIntegrationTest` | 62 tests, 342 assertions |

The tests verify observable HTTP behavior through Symfony kernels and controlled fixtures modeling Broker Cloud and Target Cloud interactions. Existing legacy OAuth tests were extended during Release A implementation; **the OAuth test sources have not changed since issue 09 began at `c62f7ca48`**. Compared with the pre-implementation base, they do include the earlier feature-specific additions and fixture updates.

Browser and e2e tests were not run, per scope. Full repository lint and unrelated integration suites were also not run. Per the user's acceptance decision, this checkpoint's readiness criterion is that the unchanged legacy authorization experience and the new server-side authorization contract both work; the two selected integration groups provide that evidence. PHPUnit emitted existing Symfony/FOS deprecation notices; the tests passed.

## Canary and fleet rollout plan

No fleet inventory or canary results were collected while producing this checkpoint, and none is required to accept Release A code readiness. Do not infer that approximately 200 installations are upgraded, reachable, or ready. The following inventory/canary procedure is a separate operational aid for a future Release B rollout, not a blocker to this issue:

1. Inventory each participating Broker Cloud and Target Cloud, identify its role and whether it is active, inactive, private, or unreachable, and record the observation time.
2. For each **active remote Target Cloud**, call its HTTPS `GET /api/server-info` endpoint and record the exact integer `oauthAuthorizationApiVersion`. Require version `1`; do not infer capability from `cloudVersion`, a successful health check, or the absence of an error.
3. Exercise a canary matrix with the Release A legacy browser OAuth flow, local Target Cloud authorization, and Broker Cloud-to-Target Cloud authorization/token exchange, including PKCE and a Target Cloud without the new capability. Confirm the latter fails clearly for the new Broker Cloud operation while its legacy flow remains usable.
4. Confirm no new UI/session behavior has been activated, ordinary login and remote WebApp tokens still work, existing grants remain usable, and consent revocation still works. Expand the rollout only after the operator reviews canary results and the inactive/private/unreachable exceptions.
5. Keep Release A as the rollback boundary. If Release B is later activated and must be rolled back, redeploy Release A without reverting compatible schema or changing existing grants, codes, or tokens. An in-flight browser flow may need to restart.

### Operator inventory checklist

Maintain one row per participating instance. This template is intentionally empty; no fleet state is asserted by this document.

| Instance / operator identifier | Role (Broker Cloud/Target Cloud/both) | Classification (active/inactive/private/unreachable) | HTTPS server-info checked at (UTC) | `oauthAuthorizationApiVersion` | Legacy flow / remote WebApp token | Broker Cloud/Target Cloud and PKCE canary | Consent revocation | Exception / follow-up |
| --- | --- | --- | --- | --- | --- | --- | --- | --- |
|  |  |  |  |  |  |  |  |  |

For private instances, record how the operator verified the exact endpoint and capability. For inactive or unreachable instances, record the reason and owner; do not count them as ready. The Release B activation gate remains closed until the fleet owner has reviewed the completed inventory and approved rollout.

## Limitations and release boundary

- This is a backend code-readiness checkpoint, not evidence that the fleet is ready or that Release A has shipped.
- The integration tests use controlled fixtures, not the participating production installations.
- No production canary, fleet capability sweep, browser/e2e test, publish, deploy, or Release B activation occurred.
- `c62f7ca48eef89bc0ee6892984a9c5411b0c96b4` is the independently deployable Release A code candidate recorded here. It is not tagged or externally published. Release B remains a separate future release boundary.
