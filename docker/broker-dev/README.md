# Broker sandbox: manual mixed-version verification

This guide operates the isolated local broker sandbox and verifies forwarding
between current and frozen SUPLA Cloud versions. It is intended for manual
browser and command-line checks, not automated end-to-end testing.

## Prerequisites and scope

- Run commands from the root of this `supla-cloud` checkout.
- Docker and Docker Compose must be available.
- The current `../supla-autodiscover` sibling checkout must exist. The sandbox
  mounts it read-only and does not modify it.
- Ensure that the normal development Compose project is not used as a sandbox
  dependency. The sandbox has its own MariaDB container, `broker_sandbox_*`
  databases, and Compose volumes.

The sandbox has no real device server: fixture devices and channels represent
mocked device communication. It uses HTTP on loopback only; HTTPS, rotating
DNS, and private-cloud URL-entry onboarding are outside its coverage. Because
the applications share a network namespace, it also does not faithfully test
source-IP trust or forwarded-client-IP trust boundaries. Cloud 3 supports only
the broker-sandbox baseline release (the release introducing this runtime
harness) and later compatible refs. Every supported ref includes the configured
local public broker URL. The frozen image takes application source exclusively
from the selected ref, including `public/index.php` and `frontend/`; the current
checkout supplies the sandbox HTTP runtime harness as external baseline wiring.
This permits building the baseline release itself, which predates the harness
files. Autodiscover is always the current sibling checkout, so
selecting a historical autodiscover version is not supported.

Cloud 3 is built with its selected version's Composer and frontend lockfiles.
The frozen runtime keeps development dependencies because the supported
development Cloud runtime and bootstrap commands require them. The builder
checks the selected source layout and runtime entrypoint before building, and
fails clearly for an unsupported compatible-looking version rather than
silently applying current-source runtime assumptions.

## Setup and lifecycle

Before setup, verify that all loopback ports are free. If a port is occupied,
stop the process using it before continuing; the command identifies both the
port and sandbox service.

```bash
docker/broker-dev/check-ports
```

All published ports are configurable. Export the desired values before every
`sandbox` command; the Compose listeners, canonical Cloud and autodiscover
URLs, OAuth callback registration, bootstrap mappings, and health output use
the same values. For example:

```bash
export BROKER_SANDBOX_CLOUD_1_PORT=18001
export BROKER_SANDBOX_CLOUD_2_PORT=18002
export BROKER_SANDBOX_CLOUD_3_PORT=18003
export BROKER_SANDBOX_AUTODISCOVER_PORT=18010
export BROKER_SANDBOX_OAUTH_CALLBACK_PORT=18090
```

When ports are overridden, replace the documented default URLs with the
corresponding exported port values in browser and `curl` commands.

Build a replacement frozen Cloud 3 image from the broker-sandbox baseline release
or a later compatible ref. A Git-ref build reads Git objects and does not
switch, clean, reset, or otherwise modify the live checkout. The build records
the replacement as pending; it does not change the active Cloud 3 image or its
state.

```bash
docker/broker-dev/sandbox frozen build --ref <baseline-release-or-later-ref>
docker/broker-dev/sandbox reset --confirm
```

Alternatively, build a frozen image from a dirty working-tree snapshot. It
includes application changes but excludes local credentials, caches,
dependencies, and generated frontend assets:

```bash
docker/broker-dev/sandbox frozen build --snapshot
docker/broker-dev/sandbox reset --confirm
```

`setup` explicitly starts and bootstraps a new sandbox using the active image.
`reset --confirm` explicitly removes only named volumes from the
`supla-broker-sandbox` Compose project, then applies a pending replacement and
bootstraps fresh sandbox data. While a replacement is pending, lifecycle and
inspection commands refuse to run so the old image cannot be combined with
incompatible state. Neither command builds an image, modifies sources, or
removes normal development databases or volumes. Use reset when replacing Cloud
3 or when a clean retry is required.

Use the following commands for routine operation:

```bash
# Start/resume without changing fixture data or the active frozen image.
# This does not wait for or trigger bootstrap.
docker/broker-dev/sandbox start

# Stop services while preserving all sandbox data and the frozen image.
docker/broker-dev/sandbox stop

# Rebuild assets for Clouds 1 and 2 only; Cloud 3 remains frozen.
docker/broker-dev/sandbox frontend

# Inspect state, logs, and the active frozen-image provenance.
docker/broker-dev/sandbox health
docker/broker-dev/sandbox logs bootstrap
docker/broker-dev/sandbox logs cloud-1 cloud-2 cloud-3 autodiscover
docker/broker-dev/sandbox frozen inspect
```

The provenance command reports the image tag, requested ref, resolved commit,
and whether it is a working-tree snapshot. A snapshot must be treated as dirty
source, not as an unmodified commit. `start` checks the persistent bootstrap
completion marker after starting services; it does not initialize anything. If
the marker is missing or bootstrap previously failed, it exits with remediation:
use `setup` for a new sandbox, or inspect bootstrap logs, correct the cause, and
use `reset --confirm` for a clean retry. `health` reports the same bootstrap
state alongside endpoint reachability.

## Topology and fixture accounts

| Service | URL | Primary fixture account |
| --- | --- | --- |
| Cloud 1 | http://localhost:8001 | `user1@example.test` |
| Cloud 2 | http://localhost:8002 | `user2@example.test` |
| Cloud 3 | http://localhost:8003 | `user3@example.test` |
| Autodiscover | http://localhost:8010 | — |
| Mail inbox | http://localhost:8025 | — |
| OAuth callback receiver | http://localhost:8090 | — |

All fixture users use password `broker-sandbox`. Each user is owned by the
Cloud in its row; do not create matching accounts on another Cloud when
testing forwarding. Bootstrap also seeds useful mocked devices/channels for
ordinary authenticated UI browsing.

## Cross-broker webapp and browser CORS verification

Use browser developer tools with the Network tab open and preserve the log.

1. Open http://localhost:8001 and log in as `user2@example.test` with password
   `broker-sandbox`.
2. Confirm that the browser remains at Cloud 1 during login. In Cloud 1 and
   Cloud 2 logs, verify that Cloud 1 performed autodiscover lookup and server-
   side broker forwarding to Cloud 2.
3. Inspect the issued token or login response and confirm its target/canonical
   URL is `http://localhost:8002`.
4. Continue using the authenticated UI. Confirm subsequent browser API calls
   are made directly to `http://localhost:8002`, complete successfully, and
   show accepted cross-origin responses under normal browser CORS enforcement.
   The Cloud 2 account's sample devices/channels should be visible, not Cloud
   1 data.
5. Repeat with `user3@example.test` through another entry broker (for example,
   Cloud 2 at http://localhost:8002), then repeat a reverse live/frozen
   direction. Confirm the address-bar behavior, token target, direct API
   destination, CORS result, and distinct account data each time.

For failures, retain the browser Network record and collect the relevant
service logs with `docker/broker-dev/sandbox logs cloud-1 cloud-2 cloud-3
autodiscover`. They should identify the entry broker, destination, and failed
stage.

## OAuth verification

`docker-compose.broker-dev.yml` is an opt-in Compose project. It is separate
from the repository's ordinary `docker-compose.yml`, including its MariaDB
container and named volume.

Before starting the sandbox, check the loopback ports:

```bash
docker/broker-dev/check-ports
```

The network-holder service publishes these loopback-only endpoints:

| Service | Default URL / port |
| --- | --- |
| Cloud 1 | http://localhost:8001 |
| Cloud 2 | http://localhost:8002 |
| Cloud 3 | http://localhost:8003 |
| Autodiscover | http://localhost:8010 |
| Mail inbox | http://localhost:8025 |
| OAuth callback receiver | http://localhost:8090 |

Every web service shares the network-holder's network namespace. Therefore
`localhost:8002` reaches Cloud 2 from a browser and from PHP in any Cloud.
This does not share a service's filesystem, database, vendor dependencies, or
runtime directory; each has its own named volumes.

Cloud 3 deliberately has no fallback build context. Build its replacement
through the lifecycle helper, then apply it only with `reset --confirm` before
starting it. The active and pending selections are stored locally in
`.broker-sandbox.env`, which is deliberately untracked so experiments do not
change repository configuration.

## Bootstrap data

`setup` and `reset --confirm` wait for the `bootstrap` service. `start` only
starts or resumes services; it never waits for or triggers bootstrap. Bootstrap
creates only the four `broker_sandbox_*` databases, runs each Cloud's own
initialization and migrations, loads the sibling autodiscover checkout's
complete SQL sequence, generates its local keys, and registers all three
canonical localhost Clouds as brokers. It then creates separate ownership
mappings for `user1@example.test`, `user2@example.test`, and
`user3@example.test`.

Bootstrap writes its completion marker only after the final ownership stage.
If any stage fails, `sandbox` exits non-zero and the service log names the
failed stage; inspect it with:

```bash
docker/broker-dev/sandbox logs bootstrap
```

After correcting the cause, use `docker/broker-dev/sandbox reset --confirm` to
retry from a clean, sandbox-only state. The autodiscover checkout is mounted
read-only throughout this process.

Bootstrap explicitly waits for all three Cloud HTTP endpoints and autodiscover
before declaring readiness. A failed command exits the bootstrap service at
its named stage and never writes the readiness marker.

## OAuth walkthrough

Bootstrap creates one enabled, hidden **development-only** public OAuth client
in autodiscover. Its redirect URI is the local callback receiver at
http://localhost:8090/callback. The client ID and secret are generated during
each clean start and persist in the sandbox bootstrap volume. Bootstrap is a
one-shot service, so retrieve them without trying to exec into its exited
container:

```bash
docker/broker-dev/sandbox oauth-credentials
```

Do not reuse these credentials outside this local sandbox. All three target
Clouds receive an autodiscover mapping for this one public client. The mappings
are intentionally resolved through the real autodiscover API during the OAuth
flow; they are not independently-created Cloud OAuth clients.

1. Copy `BROKER_SANDBOX_OAUTH_CLIENT_ID` from the command above and open this
   URL in a browser (replace `CLIENT_ID`):

   ```text
   http://localhost:8001/oauth/v2/auth?client_id=CLIENT_ID&redirect_uri=http%3A%2F%2Flocalhost%3A8090%2Fcallback&response_type=code&scope=account_r%20offline_access&state=broker-sandbox
   ```

2. On Cloud 1's broker login form, sign in as `user2@example.test` with
   password `broker-sandbox`. Cloud 1 looks up the owner through autodiscover
   and forwards authorization to Cloud 2. Approve it there. The browser lands
   on the callback receiver and displays the complete callback URL; copy its
   `code` query value.

3. Exchange that code through Cloud 3. Substitute the generated client ID,
   secret, and callback code:

   ```bash
   curl --fail --show-error -X POST http://localhost:8003/oauth/v2/token \
     -d grant_type=authorization_code \
     --data-urlencode client_id="$BROKER_SANDBOX_OAUTH_CLIENT_ID" \
     --data-urlencode client_secret="$BROKER_SANDBOX_OAUTH_CLIENT_SECRET" \
     --data-urlencode redirect_uri='http://localhost:8090/callback' \
     --data-urlencode code='CALLBACK_CODE'
   ```

   The JSON response's `target_url` is `http://localhost:8002`, confirming
   that the token belongs to Cloud 2.

4. Refresh the returned refresh token through Cloud 1:

   ```bash
   curl --fail --show-error -X POST http://localhost:8001/oauth/v2/token \
     -d grant_type=refresh_token \
     --data-urlencode client_id="$BROKER_SANDBOX_OAUTH_CLIENT_ID" \
     --data-urlencode client_secret="$BROKER_SANDBOX_OAUTH_CLIENT_SECRET" \
     --data-urlencode refresh_token='REFRESH_TOKEN'
   ```

5. Use the returned access token against its `target_url` (Cloud 2 in this
   walkthrough):

   ```bash
   curl --fail --show-error \
     -H 'Authorization: Bearer ACCESS_TOKEN' \
     http://localhost:8002/api/users/current
   ```

The callback receiver has no OAuth client UI or token storage. It only makes
the redirect and authorization code observable in the browser.

## Captured-email verification

1. Trigger an account email flow from a current live Cloud (for example, an
   account-link or password-related flow available in the authenticated UI).
2. Open http://localhost:8025 and find the captured message. Mailpit persists
   messages while services are stopped and restarted; it never delivers to an
   external recipient.
3. For a current Cloud, verify that a broker email link starts with that
   Cloud's configured local public URL (Cloud 1 is `http://localhost:8001`).
4. Confirm that Cloud 3 also uses its configured local public URL in generated
   links (by default, `http://localhost:8003`).

## Resilience and persistence checks

### Invalid remote credentials

1. At Cloud 1, attempt to log in as `user2@example.test` using an incorrect
   password.
2. Confirm the login fails rather than authenticating against a local duplicate
   account or another Cloud.
3. Inspect Cloud 1, Cloud 2, and autodiscover logs to confirm the lookup,
   destination, and authentication-failure stage.

### Unavailable user server

1. Start a new entry-broker login on Cloud 1 for `user2@example.test`.
2. Before completing the login, stop the owning Cloud:

   ```bash
   docker compose -f docker-compose.broker-dev.yml stop cloud-2
   ```

3. Complete or retry the request and confirm an observable service failure. It
   must not silently fall back to an unrelated local account.
4. Capture Cloud 1 and autodiscover logs, then resume the service:

   ```bash
   docker compose -f docker-compose.broker-dev.yml start cloud-2
   ```

### Restart persistence

1. Record a logged-in session or a previously captured Mailpit message.
2. Stop and start the sandbox without reset:

   ```bash
   docker/broker-dev/sandbox stop
   docker/broker-dev/sandbox start
   ```

3. Confirm that fixture data, the frozen-image selection, and captured mail
   persist. Confirm with `docker/broker-dev/sandbox frozen inspect` that Cloud
   3 provenance is unchanged.

Do not use `reset --confirm` for this check: it intentionally resets all
sandbox application databases and runtime state.

Rebuild live assets without changing Cloud 3:

```bash
docker/broker-dev/sandbox frontend
```

## Live Cloud runtime

Clouds 1 and 2 mount the current checkout read-only. Their Composer
dependencies, Symfony runtime state, and frontend assets are separate named
volumes, so installing dependencies inside a container never changes the host
`vendor/` directory and each Cloud has independent logs, caches, sessions, and
autodiscover metadata. PHP source changes are visible to both live Clouds
without rebuilding the image.

Both live Clouds run as brokers with the real autodiscover client, pointing at
`http://localhost:8010`; device-server communication stays mocked. They use the
dedicated `adreal` environment, which otherwise inherits the ordinary
development defaults while selecting the real autodiscover client.
They use their own canonical URL and session-cookie name. Mail is sent to the
sandbox Mailpit service. MQTT and OCR are disabled so these external
integrations cannot affect broker scenarios.

## Local mail and external services

All three Clouds send mail only to Mailpit at `smtp://127.0.0.1:1025`; it is
captured in the persistent sandbox mail volume and can be viewed at
http://localhost:8025. Mailpit is reachable only through loopback-published
ports and does not deliver messages to external recipients.

The sandbox explicitly disables MQTT and OCR, and every Cloud uses the local
autodiscover service at `http://localhost:8010`. OpenWeather data, when used by
sample state, is served through that local autodiscover service rather than a
production dependency. Push notifications remain mocked with device-server
communication, so no push provider credentials are required.

All supported Cloud versions use their configured local broker public URL in
emails. Messages remain safely captured by Mailpit; verify their links against
the matching local Cloud URL.

After the runtime/bootstrap workflow has installed dependencies, rebuild the
live frontend assets only with:

```bash
docker compose -f docker-compose.broker-dev.yml exec cloud-1 sh -lc \
  'cd frontend && npm ci && npm run build'
docker compose -f docker-compose.broker-dev.yml exec cloud-2 sh -lc \
  'cd frontend && npm ci && npm run build'
```

These commands write only to each live Cloud's `public/dist` named volume;
they do not modify the host checkout or Cloud 3.
