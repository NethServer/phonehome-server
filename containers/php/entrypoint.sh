#!/usr/bin/env sh

set -e

# read .env if present
if [ -f .env ]; then
    . .env
fi

# Bootstrap application
if [ "$1" = 'php-fpm' ]; then
    wait-for "${DB_HOST:?Missing DB_HOST}:${DB_PORT:?Missing DB_PORT}" -t 60
    if [ "$APP_ENV" = "local" ]; then
        composer i
    else
        php artisan optimize
    fi
    php artisan migrate --force
    php artisan ip-geolocation:update
elif [ "$1" = 'crond' ]; then
    wait-for "${FPM_URL:?Missing FPM_URL}:${FPM_PORT:?Missing FPM_PORT}" -t 60
fi

exec "$@"
