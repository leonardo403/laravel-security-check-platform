#!/bin/sh
set -e

echo "Composer Install"
composer install

echo "Config env"
exec php -r '\"file_exists('.env') || copy('.env.example', '.env');\"'

echo "Key generate"
exec php artisan key:generate

echo "[entrypoint] Running migrations..."
exec php artisan migrate --force

echo "NPM Install"
npm install --ignore-scripts

echo "NPM Build"
npm run build
echo "[entrypoint] Starting queue worker (scans queue) in background..."
while true; do
    exec php artisan queue:work --queue=scans,default --tries=3 --timeout=300 --sleep=1
    echo "[entrypoint] Queue worker exited ($?), restarting in 2s..."
    sleep 2
done &

echo "[entrypoint] Starting web server..."
exec php artisan serve --host=0.0.0.0 --port=8000
