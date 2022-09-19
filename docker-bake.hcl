variable "TAG" {
    default = "master"
}

variable "REGISTRY" {
    default = "docker.io"
}

variable "REPOSITORY" {
    default = "tbaile/phonehome"
}

target "app" {
    dockerfile = "containers/php/Containerfile"
    platforms = ["linux/amd64"]
    target = "production"
    cache-from = ["type=registry,ref=${REGISTRY}/${REPOSITORY}-app:cache"]
    context = "."
}

target "app-develop" {
    inherits = ["app"]
    tags = ["${REGISTRY}/${REPOSITORY}-app:master"]
    output = ["type=docker"]
}

target "app-release" {
    inherits = ["app"]
    cache-to = ["type=registry,ref=${REGISTRY}/${REPOSITORY}-app:cache,mode=max"]
    output = ["type=registry"]
}

target "web" {
    dockerfile = "containers/nginx/Containerfile"
    platforms = ["linux/amd64"]
    cache-from = ["type=registry,ref=${REGISTRY}/${REPOSITORY}-web:cache"]
    context = "."
}

target "web-develop" {
    inherits = ["web"]
    tags = ["${REGISTRY}/${REPOSITORY}-web:master"]
    target = "production"
    output = ["type=docker"]
}

target "web-release" {
    inherits = ["web"]
    target = "production"
    cache-to = ["type=registry,ref=${REGISTRY}/${REPOSITORY}-web:cache,mode=max"]
    output = ["type=registry"]
}

group "develop" {
    targets = ["app-develop", "web-develop"]
}

target "testing" {
    inherits = ["app"]
    target = "testing"
}

group "release" {
    targets = ["app-release", "web-release"]
}
