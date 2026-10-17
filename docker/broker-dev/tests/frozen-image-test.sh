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
DOCKER
chmod +x "$fake_docker"

assert_contains() {
    grep -F -- "$2" "$1" >/dev/null || {
        printf 'Expected %s to contain: %s\n' "$1" "$2" >&2
        exit 1
    }
}

if PATH="$fixture:$PATH" "$root/docker/broker-dev/frozen-image" build --ref v26.03.02 >"$fixture/legacy.out" 2>&1; then
    echo 'Expected legacy layout build to fail' >&2
    exit 1
fi
assert_contains "$fixture/legacy.out" 'Unsupported Cloud layout'
assert_contains "$fixture/legacy.out" 'broker-sandbox baseline release or later'

compatible_without_harness=$(git -C "$root" rev-parse cf720568^)
if git -C "$root" cat-file -e "$compatible_without_harness:docker/broker-dev/http-runtime/entrypoint.sh" 2>/dev/null; then
    echo 'Selected compatible fixture unexpectedly contains the runtime harness' >&2
    exit 1
fi

FAKE_DOCKER_LOG="$fixture/docker.log" PATH="$fixture:$PATH" "$root/docker/broker-dev/frozen-image" build --ref "$compatible_without_harness" --tag test-frozen
assert_contains "$fixture/docker.log" 'build --file'
assert_contains "$fixture/docker.log" '--tag test-frozen'
assert_contains "$fixture/docker.log" 'test-frozen'
assert_contains "$fixture/docker.log" "$compatible_without_harness"
assert_contains "$fixture/docker.log" '--build-context frozen-source='
assert_contains "$fixture/docker.log" '--build-context sandbox-harness='

FAKE_DOCKER_LOG="$fixture/docker.log" PATH="$fixture:$PATH" "$root/docker/broker-dev/frozen-image" inspect --tag test-frozen >"$fixture/inspect.out"
assert_contains "$fixture/docker.log" 'image inspect --format'
assert_contains "$fixture/docker.log" 'test-frozen'

echo 'frozen-image tests passed'
