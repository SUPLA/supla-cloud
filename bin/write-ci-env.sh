#!/bin/sh
# Writes .env.test.local with database connection parameters for CI runs.
#
# Reason: Drone's pipeline-level and step-level `environment:` blocks do not
# propagate reliably to PHP processes in our supla/supla-cloud:ci-php8.2 image
# (verified empirically — both shell `$DATABASE_HOST` and PHP `getenv()` return
# empty). Writing the values to .env.test.local bypasses env-var propagation
# entirely: Symfony Dotenv loads the file at the very start of every Symfony
# process (phpunit, bin/console …) and applies it with the highest priority
# of all .env files, so DBAL connects to the right host regardless of what
# the surrounding shell environment looks like.
#
# Defaults match the `database` service definition in .drone.yml.
set -eu

target="${1:-.env.test.local}"

cat > "$target" <<EOF
DATABASE_HOST=${DATABASE_HOST:-database}
DATABASE_PORT=${DATABASE_PORT:-3306}
DATABASE_NAME=${DATABASE_NAME:-supla_test}
DATABASE_USER=${DATABASE_USER:-root}
DATABASE_PASSWORD=${DATABASE_PASSWORD:-drone}
EOF

echo "wrote $target:"
sed 's/^/  /' "$target"
