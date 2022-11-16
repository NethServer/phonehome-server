# PhoneHome
Complete rewrite from ground-up of [NethServer/nethserver-phonehome](https://github.com/NethServer/nethserver-phonehome), powered by [Laravel](https://laravel.com/).

[![Development Pipeline](https://github.com/NethServer/phonehome-server/actions/workflows/development.yml/badge.svg?event=push)](https://github.com/NethServer/phonehome-server/actions/workflows/development.yml)

- [PhoneHome](#phonehome)
  - [Development](#development)
    - [Application Environment](#application-environment)
    - [Laravel Sail](#laravel-sail)
      - [Requirements](#requirements)
      - [Initial setup of development environment](#initial-setup-of-development-environment)
      - [Using Sail](#using-sail)
    - [First application setup](#first-application-setup)
    - [GeoIP2](#geoip2)
  - [Testing](#testing)
  - [Build images](#build-images)
    - [Requirements](#requirements-1)
    - [Build files](#build-files)
    - [Build process](#build-process)
    - [Environment variables](#environment-variables)
    - [Release process](#release-process)
    - [Production environment](#production-environment)
  - [Migration](#migration)

## Development
### Application Environment
To get started, the application needs an environment to run on, to do that simply copy the `.env.example` to `.env`:
```bash
cp .env.example .env
```
inside the file are all the variables needed for the framework to work properly.

If you're planning to develop with the included development environment, you can simply ignore the content of the file, since everything is set up beforehand, otherwise you'll need to edit the file accordingly and then go to [first application setup](#first-application-setup).

More info on configuration can be found [in the docs](https://laravel.com/docs/9.x/configuration) or simply browsing the `config` directory.

### Laravel Sail
#### Requirements
Laravel sail is the development environment that Laravel provides by default. To use it and to develop locally, the following software is required:
 - [Docker Engine](https://docs.docker.com/engine/install/)
 - [Docker Compose](https://docs.docker.com/compose/install/linux/)

#### Initial setup of development environment
Laravel comes with a development environment by default that is called [`laravel/sail`](https://laravel.com/docs/9.x/sail). The tool is provided through `composer`, run the following command to get started:
```bash
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v $(pwd):/var/www/html \
    -w /var/www/html \
    laravelsail/php81-composer:latest \
    composer install --ignore-platform-reqs
```
this will install all PHP dependencies, but most importantly installs `sail`.

#### Using Sail
Once `sail` is installed, you can manage the development environment with it, to get everything up and running simply use:
```bash
./vendor/bin/sail up -d
```
to shut everything down, run:
```bash
./vendor/bin/sail down
```
this tool is the heart of all the development environment, is built to handle and manage the development process, for example, to simply interact with PHP, run:
```
./vendor/bin/sail php --version
```
all the other available commands can be found in the [`laravel/sail` docs](https://laravel.com/docs/9.x/sail).

### First application setup
Once that the development environment is ready, run the following commands to finish the development:
```bash
# Generate APP_KEY in the .env
./vendor/bin/sail artisan key:generate
# Migrate the database
./vendor/bin/sail artisan migrate
```
And you're set up for now, application should be successfully available in the url given by your development environment (if you're using `sail` you will find the application at `http://localhost`).

If you need to resolve locations for IPs, please follow the brief guide on how to setup [GeoIP2](#geoip2).

### GeoIP2
To achieve the ip-to-country conversion additional setup is required, you'll need a valid licence key from [MaxMind](https://www.maxmind.com). Simply register to the platform and then, in your account page, go to "Manage License Keys".

Create a licence key by confirming that it will not be used for GeoIpUpdate. Now copy the key in the .env file at `GEOIP_TOKEN` entry.

To check if the variable is set up, run this command to install the latest database release:
```bash
./vendor/bin/sail artisan app:geoip:download
```
the database should be now downloaded inside `storage/app/GeoLite2-Country`, no additional steps are required.

## Testing
The project uses [Pest](https://pestphp.com/) to run the testing for both Unit testing and Feature testing. To run the tests, simply run:
```bash
./vendor/bin/sail artisan test
# or, with code coverage
./vendor/bin/sail artisan test --coverage
```
remember that you'll need a working development setup to run all the tests, since many of them are database-related.

To create a new *Test file, simply run the following commands:
```bash
# Create a Unit test
./vendor/bin/sail artisan make:test --pest --unit FancyTest
# Create a Feature test
./vendor/bin/sail artisan make:test --pest FancyTest
```
Additional resources on how testing works can be found in [Laravel Docs](https://laravel.com/docs/9.x/testing#main-content) and [Pest Docs](https://pestphp.com/docs/writing-tests).

## Build images
### Requirements
To build the images, `docker buildx` is required, if you have Docker Engine 18.09 or above, it is included, otherwise please follow official documentation on how to install buildx at [Docker Docs](https://docs.docker.com/build/buildx/install/).

### Build files
Build-related files can be found in the following directories:
 - `containers/nginx`: contains the Dockerfile and the configuration to build the *-web image for the project;
 - `containers/php`: contains the Dockerfile and the configuration to build the *-app image for the project;
 - `containers/docker-compose.yml`: emulates a production environment using the previous mentioned images;
 - `docker-bake.hcl`: defines all the build processes and targets, more info on that can be found in the [Docker Bake Docs](https://docs.docker.com/build/customize/bake/).

### Build process
To build the images, you'll have available few commands, the main one is
```bash
docker buildx bake
# the previous command is just an alias to
docker buildx bake develop
```
that allows you to build the production images and save them inside docker.

Additional commands are:
```bash
# Run the testing inside the production image
# No image is exported to docker
docker buildx bake testing
# You can even specify a target or a group defined inside the docker-bake.hcl
# For example, to build only the web image for development
docker buildx bake web-develop
```

### Environment variables
There are few variables that handle the tags of the generated images:
 - `REGISTRY`: the registry that the image will be pushed to, will be appended before the `REPOSITORY` on every tag. Defaults to 'ghcr.io';
 - `REPOSITORY`: the name of the image, defaults to 'nethserver/phonehome-server';
 - `TAG`: tag of the produced image, defaults to 'latest'.

### Release process
> *The relase process is completely automated by GitHub Actions (release tags too), the following is only reference to the process used by the pipeline.*

Inside the `docker-bake.hcl` there are some entries that refer to *release, they are special ones that publish the images (and cache) built directly to the registry.

Make sure when using this commands you're logged in to the used `REGISTRY`, otherwise the push will fail.

Be wary that only one tag is produced by the script, additional tags will need to be provided by using [`docker tag`](https://docs.docker.com/engine/reference/commandline/tag/).

### Production environment
Inside the `deploy/docker-compose` folder, a `docker-compose.yml` is provided to emulate a production environment using upstream images or `docker buildx bake develop` images.

Simply enter the `deploy/docker-compose` folder, copy the given `deploy/docker-compose/.env.example` to `deploy/docker-compose/.env` and just add `APP_KEY` and `GEOIP_TOKEN`. Once done you can just run `docker-compose up -d` and a production environment will be running on `http://localhost` in no time.

## Migration
To migrate the data from the [old phonehome](https://github.com/NethServer/nethserver-phonehome) the command `app:phonehome:migrate` is provided, to configure the database source of the data, the 'migration' entry in `config/database.php` is provided, that can be overridden by environment.
