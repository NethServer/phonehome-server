target "base" {
    target = "production"
    context = "."
    output = ["type=docker"]
}

target "app" {
    inherits = ["base"]
    dockerfile = "containers/php/Dockerfile"
    cache-from = [
        "type=registry,ref=ghcr.io/nethserver/phonehome-server-app:master-cache"
    ]
}

target "app-develop" {
    inherits = ["app"]
    tags = [
        "ghcr.io/nethserver/phonehome-server-app:latest"
    ]
}

target "web" {
    inherits = ["base"]
    dockerfile = "containers/nginx/Dockerfile"
    cache-from = [
        "type=registry,ref=ghcr.io/nethserver/phonehome-server-web:master-cache"
    ]
}

target "web-develop" {
    inherits = ["web"]
    tags = [
        "ghcr.io/nethserver/phonehome-server-web:latest"
    ]
}

target "testing" {
    inherits = ["app"]
    target = "testing"
    output = [""]
}

group "develop" {
    targets = ["app-develop", "web-develop"]
}

group "default" {
    targets = ["develop"]
}
