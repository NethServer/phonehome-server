#!/usr/bin/env sh

registry=${REGISTRY:-docker.io}
repository=${REPOSITORY:-tbaile/phonehome}

if [ -z "$1" ]; then
    echo "No action specified."
    echo "Available actions are: 'develop'."
    exit 1
else
    case "$1" in
    develop)
        current_branch=$(git rev-parse --abbrev-ref HEAD)
        nginx_tag="$registry/$repository:$current_branch"
        echo "Build nginx image as: $nginx_tag"
        buildah build --file "containers/nginx/Containerfile" \
            --target production \
            --platform linux/amd64 \
            --layers \
            --jobs 0 \
            --tag "$nginx_tag" \
            --force-rm
        echo "Image nginx built and tagged: $nginx_tag."
        ;;
    *)
        echo "Unknown action: $1"
        exit 1
        ;;
    esac
fi
