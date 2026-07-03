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
elif [ "$1" = 'scheduler' ] || [ "$1" = 'worker' ]; then
    wait-for "${FPM_URL:?Missing FPM_URL}:${FPM_PORT:?Missing FPM_PORT}" -t 60
    if [ "$APP_ENV" != "local" ]; then
        php artisan optimize
    fi
    if [ "$1" = 'scheduler' ]; then
        cmd="php artisan schedule:work --whisper"
    else
        cmd="php artisan queue:work --sleep=3 --tries=3 --timeout=0"
    fi
    if [ "$(id -u)" = '0' ]; then
        exec su -s /bin/sh www-data -c "$cmd"
    fi
    set -- $cmd
fi

exec "$@"
