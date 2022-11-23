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
        "type=registry,ref=${REGISTRY}/${REPOSITORY}-app:master-cache",
        "type=registry,ref=${REGISTRY}/${REPOSITORY}-app:${TAG}-cache"
    ]
}

target "app-release" {
    inherits = ["app"]
    tags = [
        "${REGISTRY}/${REPOSITORY}-app:${TAG}"
    ]
    cache-to = [
        "type=registry,ref=,mode=max"
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
        "type=registry,ref=${REGISTRY}/${REPOSITORY}-web:master-cache",
        "type=registry,ref=${REGISTRY}/${REPOSITORY}-web:${TAG}-cache"
    ]
}

target "web-release" {
    inherits = ["web"]
    tags = [
        "${REGISTRY}/${REPOSITORY}-web:${TAG}"
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

group "default" {
    targets = ["develop"]
}
