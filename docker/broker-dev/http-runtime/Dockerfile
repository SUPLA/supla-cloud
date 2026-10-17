FROM composer:2.8.2 AS composer

FROM php:8.4-apache-bookworm

RUN apt-get update \
    && apt-get install --no-install-recommends -y curl mariadb-client libcurl4-openssl-dev libfreetype6-dev libicu-dev libjpeg62-turbo-dev libpng-dev libzip-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" curl gd intl pdo_mysql zip \
    && a2enmod headers rewrite \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer /usr/bin/composer /usr/local/bin/composer
COPY docker/broker-dev/http-runtime/entrypoint.sh /usr/local/bin/broker-sandbox-apache

ENTRYPOINT ["broker-sandbox-apache"]
