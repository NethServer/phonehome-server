# PhoneHome

Complete rewrite from ground-up of [NethServer/nethserver-phonehome](https://github.com/NethServer/nethserver-phonehome), powered by [Laravel](https://laravel.com/).

## Development Environment Setup

To run a development environment, `laravel/sail` is being used, after clone, get in the cloned directory and run this commands to install the needed vendor dependencies. Remember that you'll need [Docker Engine](https://docs.docker.com/engine/install/) installed.

```bash
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v $(pwd):/var/www/html \
    -w /var/www/html \
    laravelsail/php81-composer:latest \
    composer install --ignore-platform-reqs \
    && cp .env.example .env
```

Afterwards you can just call the `./vendor/bin/sail up -d` to build the development environment. More info can be found in [Sail Docs](https://laravel.com/docs/9.x/sail).
