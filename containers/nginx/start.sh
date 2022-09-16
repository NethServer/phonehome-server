#!/usr/bin/env sh
set -e

# This ensures the command "nginx" waits, while everything else runs without delays.
if [ "$1" = "nginx" ]; then
    echo "Wait for PHP backend to come up..."
    wait-for -t 30 "app:9000"
fi
exec /docker-entrypoint.sh "$@"
