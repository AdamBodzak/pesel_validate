FROM php:8.2-fpm-alpine

ARG HOST_UID=1000
ARG HOST_GID=1000

COPY --from=mlocati/php-extension-installer:2 /usr/bin/install-php-extensions /usr/local/bin/
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

RUN install-php-extensions intl opcache pdo_pgsql zip

COPY conf.d/app.ini /usr/local/etc/php/conf.d/zz-app.ini

# Run as the host user so files created in the mounted volume keep host ownership
RUN addgroup -g ${HOST_GID} app \
    && adduser -D -u ${HOST_UID} -G app app

USER app

WORKDIR /var/www/app
