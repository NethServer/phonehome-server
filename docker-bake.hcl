variable "TAG" {
    default = "master"
}

variable "REPOSITORY" {
    default = "tbaile/phonehome"
}

variable "LATEST" {
    default = false
}

target "base" {
    target = "production"
    context = "."
}

target "app" {
    inherits = ["base"]
    dockerfile = "containers/php/Containerfile"
    cache-from = [
        "type=registry,ref=${REPOSITORY}-app:latest",
        "type=registry,ref=${REPOSITORY}-app:master",
        "type=registry,ref=${REPOSITORY}-app:master-cache",
        "type=registry,ref=${REPOSITORY}-app:${TAG}",
        "type=registry,ref=${REPOSITORY}-app:${TAG}-cache"
    ]
}

target "app-release" {
    inherits = ["app"]
    tags = [
        equal(true, LATEST) ? "${REPOSITORY}-app:latest" : "",
        "${REPOSITORY}-app:${TAG}"
    ]
    cache-to = [
        "type=registry,ref=${REPOSITORY}-app:${TAG}-cache,mode=max"
    ]
    output = ["type=registry"]
}

target "app-develop" {
    inherits = ["app"]
    tags = ["${REPOSITORY}-app:latest"]
    output = ["type=docker"]
}

target "web" {
    inherits = ["base"]
    dockerfile = "containers/nginx/Containerfile"
    cache-from = [
        "type=registry,ref=${REPOSITORY}-web:latest",
        "type=registry,ref=${REPOSITORY}-web:master",
        "type=registry,ref=${REPOSITORY}-web:master-cache",
        "type=registry,ref=${REPOSITORY}-web:${TAG}",
        "type=registry,ref=${REPOSITORY}-web:${TAG}-cache"
    ]
}

target "web-release" {
    inherits = ["web"]
    tags = [
        equal(true, LATEST) ? "${REPOSITORY}-web:latest" : "",
        "${REPOSITORY}-web:${TAG}"
    ]
    cache-to = [
        "type=registry,ref=${REPOSITORY}-web:${TAG}-cache,mode=max"
    ]
    output = ["type=registry"]
}

target "web-develop" {
    inherits = ["web"]
    tags = ["${REPOSITORY}-web:latest"]
    output = ["type=docker"]
}

target "testing" {
    inherits = ["app-develop"]
    target = "testing"
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
