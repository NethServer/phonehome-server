target "app-production" {
    dockerfile = "containers/php/Dockerfile"
    target     = "production"
    tags       = [
        "ghcr.io/nethserver/phonehome-server-app:latest"
    ]
}

target "web-production" {
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

group "deploy" {
    targets = ["default", "ns8"]
}

group "default" {
    targets = ["app-production", "web-production"]
}
