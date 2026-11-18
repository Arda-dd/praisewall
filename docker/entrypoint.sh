#!/bin/sh
# Prepares the storage volume and the database before the web server starts.
set -e
cd /var/www/html

if [ -z "$APP_KEY" ]; then
    echo 'Praisewall: APP_KEY is not set. Generate one with: echo "base64:$(openssl rand -base64 32)"' >&2
    exit 1
fi

mkdir -p storage/database storage/app/public storage/logs \
    storage/framework/cache/data storage/framework/sessions storage/framework/views

if [ "$DB_CONNECTION" = "sqlite" ] && [ ! -f "$DB_DATABASE" ]; then
    touch "$DB_DATABASE"
fi

php artisan migrate --force --no-interaction
php artisan storage:link --force > /dev/null 2>&1 || true
php artisan optimize
