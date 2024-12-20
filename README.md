# PhoneHome
Complete rewrite from ground-up of [NethServer/nethserver-phonehome](https://github.com/NethServer/nethserver-phonehome), powered by [Laravel](https://laravel.com/).

[![Development Pipeline](https://github.com/NethServer/phonehome-server/actions/workflows/development.yml/badge.svg?event=push)](https://github.com/NethServer/phonehome-server/actions/workflows/development.yml)

## Development

### First steps

To get started, the application needs an environment to run on, to do that simply copy the `.env.example` to `.env`:
```bash
cp .env.example .env
```
inside the file are all the variables needed for the framework to work properly.

Here's a brief explanation of the variables:

- `APP_NAME`: The name of your application, can be left as is.
- `APP_ENV`: The environment your application is running in (e.g., local, production).
- `APP_KEY`: The encryption key used by
  Laravel. [More info](https://laravel.com/docs/11.x/configuration#application-key)
- `APP_DEBUG`: Boolean value that determines if debug mode is enabled.
- `APP_URL`: The URL of your application.
- `LOG_CHANNEL`: The logging channel used by the application. Keep stderr for Docker.
- `LOG_DEPRECATIONS_CHANNEL`: The logging channel for deprecation warnings. Keep stderr for Docker.
- `LOG_LEVEL`: The minimum log level for messages to be logged.
- `DB_CONNECTION`: We're using pgsql for PostgreSQL.
- `DB_HOST`: The hostname of your database server.
- `DB_PORT`: The port your database server is running on.
- `DB_DATABASE`: The name of your database.
- `DB_USERNAME`: The username used to connect to your database.
- `DB_PASSWORD`: The password used to connect to your database.
- `GF_INSTANCE_NAME`: The name of the Grafana instance.
- `GF_SECURITY_ADMIN_PASSWORD`: The password for the Grafana admin user.
- `GRAFANA_DATABASE_USERNAME`: The username for the read-only Grafana user.
- `GRAFANA_DATABASE_PASSWORD`: The password for the read-only Grafana user.
- `GRAFANA_PUBLIC_DASHBOARD_REDIRECT`: The URL to redirect to when accessing to the root page of phonehome.
- `GEOIP_TOKEN`: The token for GeoIP services. [More info](#geoip2)
- `UID`: The user ID for running the application. Development variable only, needed only when running dev environment.
- `GID`: The group ID for running the application. Development variable only, needed only when running dev environment.

More info on configuration can be found [in the docs](https://laravel.com/docs/11.x/configuration) or simply browsing
the `config` directory.

As a first mandatory step, you need to generate the APP_KEY, which is done by running the following command:

```bash
docker compose run --rm app php artisan key:generate
```

### Run the application

All is provided by `docker-compose.yml`, this includes:

- PostgreSQL
- PHP-fpm
- nginx
- adminer
- grafana

To start the development environment, simply run:

```bash
docker compose up -d
```

and the application will be available at `http://localhost`.

To browse the logs, you can `docker compose logs` to see the logs of all the services or
`docker compose logs <container>` to see only the logs of specific container.

To shut down the environment, simply run:

```bash
docker compose down
```

If you need to access the containers and run commands in them, use `docker compose exec <container> <command>`, for
example:

```bash
docker compose exec app php artisan migrate
```

### GeoIP2
To achieve the ip-to-country conversion additional setup is required, you'll need a valid licence key from [MaxMind](https://www.maxmind.com). Simply register to the platform and then, in your account page, go to "Manage License Keys".

Create a licence key by confirming that it will not be used for GeoIpUpdate. Now copy the key in the .env file at `GEOIP_TOKEN` entry.

To check if the variable is set up, run this command to install the latest database release:

```bash
docker compose exec app php artisan ip-geolocation:update
```
the database should be now downloaded inside `storage/app/GeoLite2-Country`, no additional steps are required.

## Testing
The project uses [Pest](https://pestphp.com/) to run the testing for both Unit testing and Feature testing. To run the tests, simply run:

```bash
docker compose exec app php artisan test
# or, with code coverage
docker compose exec app php artisan test --coverage
```
remember that you'll need a working development setup to run all the tests, since many of them are database-related.

To create a new *Test file, simply run the following commands:

```bash
# Create a Unit test
docker compose exec app php artisan make:test --pest --unit FancyTest
# Create a Feature test
docker compose exec app php artisan make:test --pest FancyTest
```
Additional resources on how testing works can be found in [Laravel Docs](https://laravel.com/docs/9.x/testing#main-content) and [Pest Docs](https://pestphp.com/docs/writing-tests).

## Build images

### Requirements
To build the images, `docker buildx` is required, if you have Docker Engine 18.09 or above, it is included, otherwise please follow official documentation on how to install buildx at [Docker Docs](https://docs.docker.com/build/buildx/install/).

### Build files
Build-related files can be found in the following directories:
 - `containers/nginx`: contains the Dockerfile and the configuration to build the *-web image for the project;
 - `containers/php`: contains the Dockerfile and the configuration to build the *-app image for the project;
- `containers/grafana`: contains the Dockerfile and the configuration to build the *-grafana image for the project;
 - `docker-bake.hcl`: defines all the build processes and targets, more info on that can be found in the [Docker Bake Docs](https://docs.docker.com/build/customize/bake/).

### Build process
To build the images, you'll have available few commands, the main one is

```bash
docker buildx bake
```
that allows you to build the production images and save them inside docker.

### Release process
> *The relase process is completely automated by GitHub Actions (release tags too), the following is only reference to the process used by the pipeline.*

Inside the pipeline there's a generation of the image schema names, which is completely automated. Will take care of
tags, multiple tags and branch pushes.

### Production environment

Inside the `deploy/docker-compose` folder, a `docker-compose.yml` is provided to emulate a production environment using
upstream images or `docker buildx bake` images.

Simply enter the `deploy/docker-compose` folder, copy the given `deploy/docker-compose/.env.example` to `deploy/docker-compose/.env` and just add `APP_KEY` and `GEOIP_TOKEN`. Once done you can just run `docker-compose up -d` and a production environment will be running on `http://localhost` in no time.

#### Initial deployment setup

