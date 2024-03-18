#!/usr/bin/env sh

set -e

wait-for "${FPM_URL:?Missing FPM_URL}:${FPM_PORT:?Missing FPM_PORT}" -t 60
