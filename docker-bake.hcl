variable "REGISTRY" {
    default = "ghcr.io"
}

variable "REPOSITORY" {
    default = "tbaile/phonehome"
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
