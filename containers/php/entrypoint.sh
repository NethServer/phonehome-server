#!/usr/bin/env sh
set -e

ROLE=${ROLE:-app}

if [ -n "$1" ]; then
    echo "Executing $1"
    exec "$@"
else
    if [ "$ROLE" = "app" ]; then
        exec php-fpm
    elif [ "$ROLE" = "setup" ]; then
        php artisan app:setup
    elif [ "$ROLE" = "scheduler" ]; then
        su -s '/bin/sh' -c 'php artisan schedule:work' www-data
    else
        echo "Unknown role '$ROLE'"
        exit 1
    fi
fi
