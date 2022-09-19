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
        nginx_tag="$registry/$repository-nginx:$current_branch"
        echo "Build nginx image as: $nginx_tag"
        buildah build --file "containers/nginx/Containerfile" \
            --target production \
            --platform linux/amd64 \
            --layers \
            --jobs 0 \
            --tag "$nginx_tag" \
            --force-rm
        echo "Image nginx built and tagged: $nginx_tag."
        php_tag="$registry/$repository-app:$current_branch"
        echo "Build php image as: $php_tag"
        buildah build --file "containers/php/Containerfile" \
            --target production \
            --platform linux/amd64 \
            --layers \
            --jobs 0 \
            --tag "$php_tag" \
            --force-rm
        echo "Image php built and tagged: $php_tag."
        ;;
    test)
        php_tag="$registry/$repository-app:testing"
        echo "Building app image targeting testing, tagging as: $php_tag"
        buildah build --file "containers/php/Containerfile" \
            --target testing \
            --platform linux/amd64 \
            --jobs 0 \
            --tag "$php_tag" \
            --force-rm
        podman image rm $php_tag
        echo "Testing successful."
            ;;
    *)
        echo "Unknown action: $1"
        exit 1
        ;;
    esac
fi
