#!/bin/sh
set -eu

if [ "${SENDIGS_DISPOSABLE_FIXTURE:-}" != 1 ]; then
    echo 'Run only inside a disposable application container joined to the fixture DB.' >&2
    exit 1
fi

cd /var/www/html
cp /fixture/database.php application/config/database.php
mkdir -p application/modules/runtime_probe/controllers
cp /fixture/Runtime_probe.php application/modules/runtime_probe/controllers/

apache2-foreground &
apache_pid=$!
trap 'kill "$apache_pid" 2>/dev/null || true' EXIT

for attempt in 1 2 3 4 5 6 7 8 9 10; do
    if curl -s -o /dev/null http://127.0.0.1/index.php/runtime_probe/absent; then
        break
    fi
    sleep 1
done

for expected in 1 2 11; do
    cookie=/tmp/probe.cookies
    rows=$expected
    if [ "$expected" = 11 ]; then
        cookie=ci_sendigs_ss=vvvvvvvvvvvvvvvvvvvvvvvvvvvvvvvv
        rows=3
    fi
    curl -sS -b "$cookie" -c /tmp/probe.cookies http://127.0.0.1/runtime_probe > /tmp/probe.json
    php -r '
        $payload = file_get_contents("/tmp/probe.json");
        $value = json_decode($payload, true);
        if (!is_array($value)) {
            throw new RuntimeException(substr(strip_tags($payload), 0, 2000));
        }
        $expected = (int) $argv[1];
        if ($value !== [
            "php" => PHP_VERSION,
            "sapi" => "apache2handler",
            "ci" => "3.1.13",
            "document_loaders" => true,
            "counter" => $expected,
            "committed" => true,
            "rolled_back" => true,
            "rows" => (int) $argv[2],
        ]) {
            throw new RuntimeException(json_encode($value));
        }
        echo "PASS HTTP, HMVC, session and MySQL: counter ", $expected, PHP_EOL;
    ' "$expected" "$rows"
done
