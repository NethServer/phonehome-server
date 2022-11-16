variable "REGISTRY" {
    default = "ghcr.io"
}

variable "REPOSITORY" {
    default = "nethserver/phonehome-server"
}

variable "TAG" {
    default = "latest"
}

target "base" {
    target = "production"
    context = "."
}

target "app" {
    inherits = ["base"]
    dockerfile = "containers/php/Dockerfile"
    cache-from = [
        "type=registry,ref=${REGISTRY}/${REPOSITORY}-app:latest",
        "type=registry,ref=${REGISTRY}/${REPOSITORY}-app:master",
        "type=registry,ref=${REGISTRY}/${REPOSITORY}-app:master-cache",
        "type=registry,ref=${REGISTRY}/${REPOSITORY}-app:${TAG}",
        "type=registry,ref=${REGISTRY}/${REPOSITORY}-app:${TAG}-cache"
    ]
}

target "app-release" {
    inherits = ["app"]
    tags = [
        "${REGISTRY}/${REPOSITORY}-app:${TAG}"
    ]
    cache-to = [
        "type=registry,ref=${REGISTRY}/${REPOSITORY}-app:${TAG}-cache,mode=max"
    ]
    output = ["type=registry"]
}

target "app-develop" {
    inherits = ["app"]
    tags = ["${REGISTRY}/${REPOSITORY}-app:latest"]
    output = ["type=docker"]
}

target "web" {
    inherits = ["base"]
    dockerfile = "containers/nginx/Dockerfile"
    cache-from = [
        "type=registry,ref=${REGISTRY}/${REPOSITORY}-web:latest",
        "type=registry,ref=${REGISTRY}/${REPOSITORY}-web:master",
        "type=registry,ref=${REGISTRY}/${REPOSITORY}-web:master-cache",
        "type=registry,ref=${REGISTRY}/${REPOSITORY}-web:${TAG}",
        "type=registry,ref=${REGISTRY}/${REPOSITORY}-web:${TAG}-cache"
    ]
}

target "web-release" {
    inherits = ["web"]
    tags = [
        "${REGISTRY}/${REPOSITORY}-web:${TAG}"
    ]
    cache-to = [
        "type=registry,ref=${REGISTRY}/${REPOSITORY}-web:${TAG}-cache,mode=max"
    ]
    output = ["type=registry"]
}

target "web-develop" {
    inherits = ["web"]
    tags = ["${REGISTRY}/${REPOSITORY}-web:latest"]
    output = ["type=docker"]
}

target "testing" {
    inherits = ["app-develop"]
    target = "testing"
    output = [""]
}

group "develop" {
    targets = ["app-develop", "web-develop"]
}

group "release" {
    targets = ["app-release", "web-release"]
}

group "default" {
    targets = ["develop"]
}

target "ns8-develop" {
    target = "production"
    context = "deploy/ns8"
    dockerfile = "Dockerfile"
    cache-from = [
        "type=registry,ref=${REGISTRY}/${REPOSITORY}:latest",
        "type=registry,ref=${REGISTRY}/${REPOSITORY}:master",
        "type=registry,ref=${REGISTRY}/${REPOSITORY}:master-cache",
        "type=registry,ref=${REGISTRY}/${REPOSITORY}:${TAG}",
        "type=registry,ref=${REGISTRY}/${REPOSITORY}:${TAG}-cache"
    ]
    tags = ["${REGISTRY}/${REPOSITORY}:latest"]
    output = ["type=docker"]
}

target "ns8-release" {
    inherits = ["ns8-develop"]
    tags = [
        "${REGISTRY}/${REPOSITORY}:${TAG}"
    ]
    cache-to = [
        "type=registry,ref=${REGISTRY}/${REPOSITORY}:${TAG}-cache,mode=max"
    ]
    output = ["type=registry"]
}
