#!/bin/sh
set -e

echo "[entrypoint] Running migrations..."
php artisan migrate --force

echo "[entrypoint] Starting queue worker (scans queue) in background..."
while true; do
    php artisan queue:work --queue=scans,default --tries=3 --timeout=300 --sleep=1
    echo "[entrypoint] Queue worker exited ($?), restarting in 2s..."
    sleep 2
done &

echo "[entrypoint] Starting web server..."
exec php artisan serve --host=0.0.0.0 --port=8000
