# Release A backend readiness checkpoint

**Status:** Selected Release A OAuth integration checks are green; the complete issue-09/repository readiness gates and fleet rollout are **not** confirmed.

**Code boundary:** `stateless-oauth-release-a` at `c62f7ca48eef89bc0ee6892984a9c5411b0c96b4` (Release A issues 01–08). This is an untagged candidate boundary, not a published or deployed release. The issue-09 checkpoint documentation is separate from that executable-code boundary.

## What Release A provides

- The existing OAuth browser authorization experience remains active: Symfony sessions, login/consent forms rendered by Twig, the existing routes, and existing code/token formats are retained. Release A does not activate the Vue authorization flow or disable sessions.
- Target-owned authorization and mapped public-client support are available through the internal authorization API. Broker calls require the broker authentication contract and independently trusted target selection.
- Authorization codes support optional S256 PKCE locally and across broker token exchange. Existing non-PKCE clients and refresh-token behavior remain supported.
- `GET /api/server-info` reports `oauthAuthorizationApiVersion: 1` when the internal authorization API is available. Capability, not `cloudVersion`, is the readiness signal for the new contract.
- Legacy broker token forwarding and the registration-only private-cloud probe use the hardened destination policy. The registration probe does not grant peer trust or permit OAuth forwarding.

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
| Legacy OAuth/session authorization and broker forwarding | `OAuthAuthenticationIntegrationTest`, `OAuthBrokerAuthorizationIntegrationTest` | 63 tests, 315 assertions; 1 existing skip |
| Target-owned and brokered future authorization, PKCE, capability/mixed-version behavior | `InternalOAuthAuthorizationIntegrationTest`, `OauthAuthorizationCapabilityIntegrationTest` | 62 tests, 342 assertions |

The tests verify observable HTTP behavior through Symfony kernels and controlled broker/target fixtures. Existing legacy OAuth tests were extended during Release A implementation; **the OAuth test sources have not changed since issue 09 began at `c62f7ca48`**. Compared with the pre-implementation base, they do include the earlier feature-specific additions and fixture updates.

Browser and e2e tests were not run, per scope. This checkpoint also does not claim that the complete repository lint, all integration suites, or a production deployment have been qualified. PHPUnit emitted existing Symfony/FOS deprecation notices; the tests passed.

## Canary and fleet rollout plan

No fleet inventory or canary results were available while producing this checkpoint. Do not infer that approximately 200 installations are upgraded, reachable, or ready. Before activating any Release B behavior, operators must:

1. Inventory each participating broker and target, identify its role and whether it is active, inactive, private, or unreachable, and record the observation time.
2. For each **active remote target**, call its HTTPS `GET /api/server-info` endpoint and record the exact integer `oauthAuthorizationApiVersion`. Require version `1`; do not infer capability from `cloudVersion`, a successful health check, or the absence of an error.
3. Exercise a canary matrix with the Release A legacy browser OAuth flow, local target authorization, and broker-to-target authorization/token exchange, including PKCE and a target without the new capability. Confirm the latter fails clearly for the new broker operation while its legacy flow remains usable.
4. Confirm no new UI/session behavior has been activated, ordinary login still works, and existing grants/tokens remain usable. Expand the rollout only after the operator reviews canary results and the inactive/private/unreachable exceptions.
5. Keep Release A as the rollback boundary. If Release B is later activated and must be rolled back, redeploy Release A without reverting compatible schema or changing existing grants, codes, or tokens. An in-flight browser flow may need to restart.

### Operator inventory checklist

Maintain one row per participating instance. This template is intentionally empty; no fleet state is asserted by this document.

| Instance / operator identifier | Role (broker/target/both) | Classification (active/inactive/private/unreachable) | HTTPS server-info checked at (UTC) | `oauthAuthorizationApiVersion` | Legacy flow canary | Broker/target canary | Exception / follow-up |
| --- | --- | --- | --- | --- | --- | --- | --- |
|  |  |  |  |  |  |  |  |

For private instances, record how the operator verified the exact endpoint and capability. For inactive or unreachable instances, record the reason and owner; do not count them as ready. The Release B activation gate remains closed until the fleet owner has reviewed the completed inventory and approved rollout.

## Limitations and release boundary

- This is a backend test checkpoint, not evidence that the fleet is ready or that Release A has shipped.
- The integration tests use controlled fixtures, not the participating production installations.
- No production canary, fleet capability sweep, browser/e2e test, publish, deploy, or Release B activation occurred.
- `c62f7ca48eef89bc0ee6892984a9c5411b0c96b4` is the independently deployable Release A code candidate recorded here. It is not tagged or externally published. Release B remains a separate future release boundary.
