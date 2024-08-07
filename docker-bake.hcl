target "_common" {
    contexts = {
        node_build = "target:node-build"
    }
}

target "node-build" {
    dockerfile = "containers/node/Dockerfile"
    target     = "build"
}

target "app-production" {
    inherits = ["_common"]
    dockerfile = "containers/php/Dockerfile"
    target     = "production"
    tags       = [
        "ghcr.io/nethserver/phonehome-server-app:latest"
    ]
}

target "web-production" {
    inherits = ["_common"]
    dockerfile = "containers/nginx/Dockerfile"
    target     = "production"
    tags       = [
        "ghcr.io/nethserver/phonehome-server-web:latest"
    ]
}

target "ns8" {
    dockerfile = "Dockerfile"
    context    = "deploy/ns8"
    target     = "production"
    tags       = [
        "ghcr.io/nethserver/phonehome-server:latest"
    ]
    labels = {
        "org.nethserver.images" : "docker.io/postgres:14.9-alpine ${target.app-production.tags[0]} ${target.web-production.tags[0]}"
    }
}

target "app" {
    dockerfile = "containers/php/Dockerfile"
    target     = "development"
    output = [
        "type=docker"
    ]
}

target "node" {
    dockerfile = "containers/node/Dockerfile"
    target     = "development"
    output = [
        "type=docker"
    ]
}

target "web" {
    dockerfile = "containers/nginx/Dockerfile"
    output = [
        "type=docker"
    ]
}

target "testing" {
    inherits = ["_common"]
    dockerfile = "containers/php/Dockerfile"
    tags = [
        "phonehome/testing:latest"
    ]
    target = "testing"
    cache-from = [
        "type=gha"
    ]
    output = [
        "type=docker"
    ]
}

group "deploy" {
    targets = ["app-production", "web-production", "ns8"]
}
