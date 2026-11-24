#!/bin/sh
set -eu

cd /var/www/html

# A new server boot rotates only short-lived relay tickets. Agent credentials and
# remembered consent remain in the database and never require reinstallation.
if [ -z "${REMOTE_RELAY_SECRET:-}" ]; then
    REMOTE_RELAY_SECRET="$(php -r 'echo bin2hex(random_bytes(32));')"
fi
export REMOTE_RELAY_SECRET
export REMOTE_BEHIND_TLS_PROXY=true
export REMOTE_RELAY_HOST=0.0.0.0
export REMOTE_HTTP_UPSTREAM_PORT=8081
export REMOTE_DROP_USER=www-data

sed -i 's/Listen [0-9]*/Listen 127.0.0.1:8081/g' /etc/apache2/ports.conf
sed -i 's/<VirtualHost \*:[0-9]*>/<VirtualHost *:8081>/g' /etc/apache2/sites-available/000-default.conf

# Apache serves PHP internally. Node receives public WebSocket frames directly,
# avoiding Apache's stalled WebSocket tunnels behind Render's HTTPS ingress.
apache2-foreground &
apache_pid=$!
node remote/relay/server.mjs &
relay_pid=$!

stop() {
    kill "$apache_pid" "$relay_pid" 2>/dev/null || true
    wait "$apache_pid" "$relay_pid" 2>/dev/null || true
}
trap 'stop; exit 0' TERM INT

# Stop the container if either server fails so Render can restart both together.
while kill -0 "$relay_pid" 2>/dev/null && kill -0 "$apache_pid" 2>/dev/null; do
    sleep 1
done
stop
exit 1
