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

target "grafana-production" {
    dockerfile = "containers/grafana/Dockerfile"
    tags       = [
        "ghcr.io/nethserver/phonehome-server-grafana:latest"
    ]
}

group "default" {
    targets = ["app-production", "web-production", "grafana-production"]
}
