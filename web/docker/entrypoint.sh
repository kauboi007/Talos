#!/bin/sh
set -eu

php artisan config:cache
php artisan route:cache
php artisan view:cache

php artisan serve --host=0.0.0.0 --port="${APP_PORT:-8000}" &
web_pid=$!

php artisan talos:cbs-worker &
worker_pid=$!

term_handler() {
    kill "$web_pid" "$worker_pid" 2>/dev/null || true
    wait "$web_pid" "$worker_pid" 2>/dev/null || true
}

trap term_handler INT TERM

while kill -0 "$web_pid" 2>/dev/null && kill -0 "$worker_pid" 2>/dev/null; do
    sleep 1
done

term_handler
exit 1
