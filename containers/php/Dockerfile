ARG PHP_EXTENSIONS="pcntl pdo_pgsql zip"
ARG PHP_EXTENSIONS_DEV="${PHP_EXTENSIONS} xdebug"
ARG PHP_EXTENSIONS_PROD="${PHP_EXTENSIONS} opcache"

ARG DEPENDENCIES="netcat-openbsd postgresql15-client procps"
ARG DEPENDENCIES_DEV="git shadow sudo bash"

# base image for all containers
FROM php:8.3.10-fpm-alpine as base
WORKDIR /var/www/html
# install extensions and packages
COPY --from=mlocati/php-extension-installer:2.3.2 /usr/bin/install-php-extensions /usr/local/sbin/install-php-extensions
COPY --from=composer:2.7.7 /usr/bin/composer /usr/local/bin/composer
# php configuration
COPY containers/php/conf.d /usr/local/etc/php/conf.d
# container cronjob configuration
RUN echo "* * * * * cd /var/www/html && php artisan schedule:run >> /dev/null 2>&1" > /etc/crontabs/www-data
# additional binaries
ADD --chmod=777 \
    --checksum=sha256:206a8f9b2177703fc5aa924d85ad6c72e82413e2d09635b4c9c82a1b65b5b3d5 \
    https://github.com/eficode/wait-for/releases/download/v2.2.4/wait-for /usr/local/bin/wait-for
# entrypoint setup
COPY --chmod=777 containers/php/entrypoint.sh /usr/local/sbin/entrypoint
ENTRYPOINT ["entrypoint"]
CMD ["php-fpm"]

# development image
FROM base AS development
ARG DEPENDENCIES_DEV
ARG PHP_EXTENSIONS_DEV
# install dependencies and configure php
RUN apk add --no-cache ${DEPENDENCIES_DEV} \
    && install-php-extensions ${PHP_EXTENSIONS_DEV} \
    && mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"  \
    && echo "ALL ALL=(ALL:ALL) NOPASSWD: ALL" > "/etc/sudoers.d/password_less_sudo"
ARG UID
ARG GID
RUN usermod -u "$UID" -s /bin/bash www-data \
    && groupmod -g "$GID" www-data
ENV XDEBUG_MODE=develop,debug,coverage
ENV XDEBUG_CONFIG="client_host=host.docker.internal"
USER www-data

# vendor files
FROM base AS vendor
COPY composer.json .
COPY composer.lock .
RUN composer install --no-scripts --no-dev --ignore-platform-reqs

# all application files
FROM base AS application
COPY app ./app
COPY bootstrap ./bootstrap
COPY config ./config
COPY database ./database
COPY public ./public
COPY resources ./resources
COPY routes ./routes
COPY --chown=www-data:www-data storage ./storage
COPY artisan .
COPY composer.json .
COPY composer.lock .

# production final image
FROM base AS production
# volume export for storage
VOLUME ["/var/www/html/storage"]
# install dependencies and configure php
ARG DEPENDENCIES
ARG PHP_EXTENSIONS_PROD
RUN apk add --no-cache ${DEPENDENCIES} \
    && install-php-extensions ${PHP_EXTENSIONS_PROD} \
    && mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
# copy application files
COPY --from=application /var/www/html /var/www/html
COPY --from=vendor /var/www/html/vendor /var/www/html/vendor
# setup applications and remove unnecessary files
RUN composer check-platform-reqs \
    && composer dump-autoload --optimize \
    && echo -n "opcache.max_accelerated_files=" >> "$PHP_INI_DIR/conf.d/opcache.ini" \
    && echo $(find . -name "*.php" | wc -l | awk '{print (int($1/1000)+2)*1000}') >> "$PHP_INI_DIR/conf.d/opcache.ini" \
    && rm /usr/local/bin/composer \
    && rm /usr/local/sbin/install-php-extensions
