#!/usr/bin/env sh

#
# Copyright (C) 2022 Nethesis S.r.l.
# SPDX-License-Identifier: GPL-3.0-or-later
#

set -e

# Set up all variables needed to build the arguments
tags=${TAGS:-ghcr.io/nethserver/phonehome-server:latest}
labels=${LABELS:-}
args=${ARGS:-}

for tag in $tags; do
    set -- "$@" --tag="$tag"
done

for label in $labels; do
    set -- "$@" --label="$label"
done

for arg in $args; do
    set -- "$@" --build-arg="$arg"
done

# Execute the buildah build command with the additional arguments given above
buildah build \
    --force-rm \
    --jobs "$(nproc)" \
    --layers \
    --file Dockerfile \
    --target production \
    "$@" \
    .

# If it's run locally, print the next commands to publish the images tagged
if [ -z "${CI}" ]; then
    echo "Manually push the images with:"
    for tag in $tags; do
        echo "podman push $tag"
    done
fi
