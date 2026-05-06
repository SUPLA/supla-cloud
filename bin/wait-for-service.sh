#!/bin/sh
# Wait for a CI service (MariaDB/MySQL or Postgres) to start accepting
# connections. Probes via PHP+PDO so the script depends only on what is
# guaranteed to be in the application image (PHP 8.x with pdo_mysql and
# pdo_pgsql), not on CLI clients like mysqladmin or pg_isready that we
# cannot assume are installed.
#
# Usage: bin/wait-for-service.sh <host> <mariadb|postgres> [timeout-seconds]
set -eu

host="${1:?usage: $0 <host> <mariadb|postgres> [timeout]}"
type="${2:?usage: $0 <host> <mariadb|postgres> [timeout]}"
timeout="${3:-60}"

case "$type" in
    mariadb|mysql)
        dsn="mysql:host=$host"
        user="${MARIADB_PROBE_USER:-root}"
        pass="${MARIADB_PROBE_PASS:-drone}"
        ;;
    postgres|postgresql)
        dsn="pgsql:host=$host;port=5432;dbname=postgres"
        user="${POSTGRES_PROBE_USER:-postgres}"
        pass="${POSTGRES_PROBE_PASS:-postgres}"
        ;;
    *)
        echo "unknown service type: $type (expected mariadb or postgres)" >&2
        exit 2
        ;;
esac

echo "wait-for-service: probing $type at $host (DSN=$dsn user=$user, timeout=${timeout}s)"

# Returns the PDO connection error message via stderr (or empty string on success);
# exit status 0 => connected, 1 => failed.
probe() {
    PROBE_DSN="$dsn" PROBE_USER="$user" PROBE_PASS="$pass" \
        php -r 'try {
            $pdo = new PDO(getenv("PROBE_DSN"), getenv("PROBE_USER"), getenv("PROBE_PASS"), [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
            $version = $pdo->query("SELECT VERSION()")->fetchColumn();
            fwrite(STDERR, $version);
            exit(0);
        } catch (Throwable $e) {
            fwrite(STDERR, $e->getMessage());
            exit(1);
        }'
}

i=1
while [ "$i" -le "$timeout" ]; do
    if msg=$(probe 2>&1); then
        echo "wait-for-service: $type at $host is ready after ${i}s, version=$msg"
        exit 0
    fi
    echo "waiting for $type at $host… ($i/$timeout): $msg"
    i=$((i + 1))
    sleep 1
done

echo "wait-for-service: $type at $host did not become ready within ${timeout}s" >&2
exit 1
