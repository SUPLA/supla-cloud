FROM composer:2.8.2 AS php-dependencies
WORKDIR /var/www/html
COPY --from=frozen-source composer.json composer.lock ./
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

FROM node:22.14.0-bookworm AS frontend
WORKDIR /var/www/html/frontend
COPY --from=frozen-source frontend/package.json frontend/package-lock.json ./
RUN npm ci
COPY --from=frozen-source frontend/ ./
COPY --from=php-dependencies /var/www/html/vendor /var/www/html/vendor
RUN npm run build

FROM php:8.4-apache-bookworm
RUN apt-get update \
    && apt-get install --no-install-recommends -y curl libcurl4-openssl-dev libfreetype6-dev libicu-dev libjpeg62-turbo-dev libpng-dev libzip-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" curl gd intl pdo_mysql zip \
    && a2enmod headers rewrite \
    && rm -rf /var/lib/apt/lists/*
COPY --from=composer:2.8.2 /usr/bin/composer /usr/local/bin/composer
WORKDIR /var/www/html
COPY --from=frozen-source . ./
COPY --from=php-dependencies /var/www/html/vendor ./vendor
COPY --from=frontend /var/www/html/public/dist ./public/dist
COPY --from=sandbox-harness entrypoint.sh /usr/local/bin/broker-sandbox-apache
ENV APP_DOCUMENT_ROOT=/var/www/html/public
ENV APP_ENV=adreal
ENV SUPLA_ACT_AS_BROKER_CLOUD=1
ENV SUPLA_BROKER_PUBLIC_URL=http://localhost:8003
ENV SESSION_COOKIE_NAME=SUPLA_BROKER_SANDBOX_8003
ENTRYPOINT ["broker-sandbox-apache"]
