#!/bin/sh
set -eu

root=$(CDPATH= cd -- "$(dirname -- "$0")/../../.." && pwd)
fixture=$(mktemp -d)
trap 'rm -rf "$fixture"' EXIT

fake_docker="$fixture/docker"
cat > "$fake_docker" <<'DOCKER'
#!/bin/sh
set -eu
printf '%s\n' "$*" >> "$FAKE_DOCKER_LOG"
if [ "${FAKE_DOCKER_BOOTSTRAP_STATUS:-}" ] && printf '%s\n' "$*" | grep -F 'ps --all --filter service=bootstrap --filter status=exited -q' >/dev/null; then
    printf '%s\n' "$FAKE_DOCKER_BOOTSTRAP_STATUS"
fi
DOCKER
chmod +x "$fake_docker"

assert_contains() {
    grep -F -- "$2" "$1" >/dev/null || {
        printf 'Expected %s to contain: %s\n' "$1" "$2" >&2
        exit 1
    }
}

assert_not_contains() {
    if grep -F -- "$2" "$1" >/dev/null; then
        printf 'Expected %s not to contain: %s\n' "$1" "$2" >&2
        exit 1
    fi
}

env_file="$fixture/sandbox.env"
printf 'BROKER_SANDBOX_FROZEN_IMAGE=chosen-cloud-3\n' > "$env_file"

fake_frozen_image="$fixture/frozen-image"
cat > "$fake_frozen_image" <<'FROZEN_IMAGE'
#!/bin/sh
set -eu
printf '%s\n' "$*" >> "$FAKE_FROZEN_IMAGE_LOG"
FROZEN_IMAGE
chmod +x "$fake_frozen_image"

if FAKE_DOCKER_LOG="$fixture/docker.log" PATH="$fixture:$PATH" BROKER_SANDBOX_ENV_FILE="$env_file" "$root/docker/broker-dev/sandbox" reset >"$fixture/reset.out" 2>&1; then
    echo 'Expected reset without confirmation to fail' >&2
    exit 1
fi
assert_contains "$fixture/reset.out" 'requires --confirm'

FAKE_FROZEN_IMAGE_LOG="$fixture/frozen-image.log" PATH="$fixture:$PATH" BROKER_SANDBOX_ENV_FILE="$env_file" BROKER_SANDBOX_FROZEN_IMAGE_COMMAND="$fake_frozen_image" "$root/docker/broker-dev/sandbox" frozen build --ref baseline --tag replacement-cloud-3
assert_contains "$fixture/frozen-image.log" 'build --ref baseline --tag replacement-cloud-3'
assert_contains "$env_file" 'BROKER_SANDBOX_FROZEN_IMAGE=chosen-cloud-3'
assert_contains "$env_file" 'BROKER_SANDBOX_PENDING_FROZEN_IMAGE=replacement-cloud-3'

if FAKE_DOCKER_LOG="$fixture/docker.log" PATH="$fixture:$PATH" BROKER_SANDBOX_ENV_FILE="$env_file" "$root/docker/broker-dev/sandbox" start >"$fixture/start-pending.out" 2>&1; then
    echo 'Expected start with a pending frozen image to fail' >&2
    exit 1
fi
assert_contains "$fixture/start-pending.out" 'pending frozen Cloud 3 image'
assert_contains "$fixture/start-pending.out" 'sandbox reset --confirm'
[ ! -f "$fixture/docker.log" ] || assert_not_contains "$fixture/docker.log" 'up -d'

: > "$fixture/docker.log"
FAKE_DOCKER_LOG="$fixture/docker.log" PATH="$fixture:$PATH" BROKER_SANDBOX_ENV_FILE="$env_file" "$root/docker/broker-dev/sandbox" reset --confirm
assert_contains "$fixture/docker.log" 'down --volumes --remove-orphans'
assert_contains "$fixture/docker.log" 'up -d'
assert_contains "$fixture/docker.log" 'wait bootstrap'
[ -d "$root/public/dist" ] || {
    echo 'Expected sandbox reset to prepare the public/dist volume mountpoint' >&2
    exit 1
}
[ -d "$root/../supla-autodiscover/vendor" ] && [ -d "$root/../supla-autodiscover/var" ] || {
    echo 'Expected sandbox reset to prepare autodiscover volume mountpoints' >&2
    exit 1
}
assert_contains "$env_file" 'BROKER_SANDBOX_FROZEN_IMAGE=replacement-cloud-3'
assert_not_contains "$env_file" 'BROKER_SANDBOX_PENDING_FROZEN_IMAGE='

: > "$fixture/docker.log"
FAKE_DOCKER_LOG="$fixture/docker.log" PATH="$fixture:$PATH" BROKER_SANDBOX_ENV_FILE="$env_file" "$root/docker/broker-dev/sandbox" start
assert_contains "$fixture/docker.log" '--env-file'
assert_contains "$fixture/docker.log" 'up -d'
assert_not_contains "$fixture/docker.log" 'wait bootstrap'
assert_not_contains "$fixture/docker.log" 'build'
assert_contains "$fixture/docker.log" 'ps --all --filter service=bootstrap --filter status=exited -q'

: > "$fixture/docker.log"
if FAKE_DOCKER_LOG="$fixture/docker.log" FAKE_DOCKER_BOOTSTRAP_STATUS=bootstrap-failed PATH="$fixture:$PATH" BROKER_SANDBOX_ENV_FILE="$env_file" "$root/docker/broker-dev/sandbox" start >"$fixture/start-failed.out" 2>&1; then
    echo 'Expected start with failed bootstrap to fail' >&2
    exit 1
fi
assert_contains "$fixture/start-failed.out" 'bootstrap previously failed'
assert_contains "$fixture/start-failed.out" 'sandbox reset --confirm'

: > "$fixture/docker.log"
FAKE_DOCKER_LOG="$fixture/docker.log" PATH="$fixture:$PATH" BROKER_SANDBOX_ENV_FILE="$env_file" "$root/docker/broker-dev/sandbox" setup
assert_contains "$fixture/docker.log" 'up -d'
assert_contains "$fixture/docker.log" 'wait bootstrap'
assert_not_contains "$fixture/docker.log" 'build'

: > "$fixture/docker.log"
printf 'BROKER_SANDBOX_PENDING_FROZEN_IMAGE=next-replacement-cloud-3\n' >> "$env_file"
FAKE_DOCKER_LOG="$fixture/docker.log" PATH="$fixture:$PATH" BROKER_SANDBOX_ENV_FILE="$env_file" "$root/docker/broker-dev/sandbox" reset --confirm
assert_contains "$fixture/docker.log" 'down --volumes --remove-orphans'
assert_contains "$fixture/docker.log" 'up -d'
assert_contains "$fixture/docker.log" 'wait bootstrap'
assert_not_contains "$fixture/docker.log" 'build'

: > "$fixture/docker.log"
FAKE_DOCKER_LOG="$fixture/docker.log" PATH="$fixture:$PATH" BROKER_SANDBOX_ENV_FILE="$env_file" "$root/docker/broker-dev/sandbox" reset --confirm
assert_contains "$fixture/docker.log" 'down --volumes --remove-orphans'
assert_contains "$fixture/docker.log" 'up -d'
assert_contains "$fixture/docker.log" 'wait bootstrap'

assert_contains "$root/docker/broker-dev/bootstrap" 'public OAuth client and mappings'
[ -x "$root/docker/broker-dev/bootstrap" ] || {
    echo 'Expected bootstrap entrypoint to be executable' >&2
    exit 1
}
assert_contains "$root/docker/broker-dev/bootstrap.Dockerfile" 'docker-compose'
assert_contains "$root/docker/broker-dev/bootstrap" 'public-clients:create'
assert_contains "$root/docker/broker-dev/bootstrap" 'public-clients:update'
assert_contains "$root/docker/broker-dev/bootstrap" 'mapped-client'
assert_contains "$root/docker/broker-dev/README.md" 'OAuth walkthrough'
assert_contains "$root/docker/broker-dev/README.md" 'http://localhost:8090/callback'
assert_contains "$root/docker/broker-dev/README.md" 'Cloud 3'
assert_contains "$root/docker/broker-dev/README.md" 'sandbox oauth-credentials'
assert_contains "$root/docker-compose.broker-dev.yml" 'APP_ENV: adreal'
assert_not_contains "$root/docker-compose.broker-dev.yml" 'APP_ENV: brokertesting'
assert_contains "$root/docker/broker-dev/frozen-image.Dockerfile" 'ENV APP_ENV=adreal'
assert_not_contains "$root/docker/broker-dev/frozen-image.Dockerfile" 'brokertesting'
assert_contains "$root/config/services.yaml" 'when@adreal:'
assert_not_contains "$root/config/services.yaml" 'when@brokertesting:'

: > "$fixture/docker.log"
FAKE_DOCKER_LOG="$fixture/docker.log" PATH="$fixture:$PATH" BROKER_SANDBOX_ENV_FILE="$env_file" "$root/docker/broker-dev/sandbox" health
assert_contains "$fixture/docker.log" 'ps'
assert_contains "$fixture/docker.log" 'image inspect'
assert_contains "$fixture/docker.log" 'next-replacement-cloud-3'
assert_contains "$fixture/docker.log" 'ps --all --filter service=bootstrap --filter status=exited -q'

echo 'sandbox lifecycle tests passed'
