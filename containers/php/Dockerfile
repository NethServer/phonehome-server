# base image for all containers
FROM php:8.3.3-fpm-alpine as base
WORKDIR /var/www/html
# install extensions and packages
COPY --from=mlocati/php-extension-installer:2.2.2 /usr/bin/install-php-extensions /usr/local/sbin/
RUN apk add --no-cache \
        netcat-openbsd \
        postgresql15-client \
        procps \
    && install-php-extensions \
        pcntl \
        pdo_pgsql \
        pgsql \
        redis \
        zip
COPY --from=composer:2.7.2 /usr/bin/composer /usr/local/bin/composer

FROM base as development
# install xdebug and additional packages
RUN apk add --no-cache \
        bash \
        git \
        shadow \
        sudo \
    && install-php-extensions \
        xdebug \
    # configuring php and passwordless sudo
    && mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini" \
    && echo "ALL ALL=(ALL:ALL) NOPASSWD: ALL" > "/etc/sudoers.d/password_less_sudo"
ADD --chmod=777 \
    --checksum=sha256:206a8f9b2177703fc5aa924d85ad6c72e82413e2d09635b4c9c82a1b65b5b3d5 \
    https://github.com/eficode/wait-for/releases/download/v2.2.4/wait-for /usr/local/bin/wait-for
COPY --chmod=777 containers/php/entrypoint.sh /usr/local/sbin/entrypoint
ENTRYPOINT ["entrypoint"]
CMD ["php-fpm"]
ARG UID
ARG GID
RUN usermod -u "$UID" www-data \
    && groupmod -g "$GID" www-data
ENV XDEBUG_MODE=develop,debug,coverage
ENV XDEBUG_CONFIG="client_host=host.docker.internal"
USER www-data

# vendor files
FROM base as vendor
COPY composer.json .
COPY composer.lock .
RUN composer i --no-scripts --no-dev --ignore-platform-reqs

# vendor dev files
FROM vendor as vendor_test
RUN composer i --no-scripts --ignore-platform-reqs

# all application files
FROM base as application
COPY app ./app
COPY bootstrap ./bootstrap
COPY config ./config
COPY database ./database
COPY public ./public
COPY resources ./resources
COPY routes ./routes
COPY storage ./storage
COPY artisan .
COPY composer.json .
COPY composer.lock .

FROM base as testing
RUN install-php-extensions xdebug
COPY --from=application /var/www/html /var/www/html
COPY --from=vendor_test /var/www/html/vendor /var/www/html/vendor
COPY tests ./tests
COPY phpunit.xml .
RUN composer check-platform-reqs \
    && composer dump-autoload \
    && chown -R www-data:www-data storage
USER www-data
ENV XDEBUG_MODE=coverage
CMD ["php", "artisan", "test", "--coverage-cobertura", "coverage.cobertura.xml"]

FROM base as production
# container php extensions, packages and using official production configuration
RUN install-php-extensions \
        opcache \
    && apk add --no-cache \
        netcat-openbsd \
        procps \
    && echo "access.log = /dev/null" >> /usr/local/etc/php-fpm.d/zz-docker.conf \
    && mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini" \
    && echo "* * * * * cd /var/www/html && php artisan schedule >> /dev/null 2>&1" > /etc/crontabs/www-data
# php configuration
COPY containers/php/conf.d /usr/local/etc/php/conf.d
# additional binaries and entrypoint
ADD --chmod=777 \
    --checksum=sha256:206a8f9b2177703fc5aa924d85ad6c72e82413e2d09635b4c9c82a1b65b5b3d5 \
    https://github.com/eficode/wait-for/releases/download/v2.2.4/wait-for /usr/local/bin/wait-for
COPY --chmod=777 containers/php/entrypoint.sh /usr/local/sbin/entrypoint
ENTRYPOINT ["entrypoint"]
CMD ["php-fpm"]
# container ready, copy over application and do software bootstrap
COPY --from=application /var/www/html /var/www/html
COPY --from=vendor /var/www/html/vendor /var/www/html/vendor
ARG COMPOSER_ALLOW_SUPERUSER=1
RUN chown -R www-data:www-data storage \
    && composer dump-autoload --optimize \
    && echo -n "opcache.max_accelerated_files=" >> "$PHP_INI_DIR/conf.d/opcache.ini" \
    && echo $(find . -name "*.php" | wc -l | awk '{print (int($1/1000)+2)*1000}') >> "$PHP_INI_DIR/conf.d/opcache.ini" \
    && composer check-platform-reqs \
    && rm /usr/local/bin/composer \
    && rm /usr/local/sbin/install-php-extensions
VOLUME ["/var/www/html/bootstrap", "/var/www/html/storage"]
