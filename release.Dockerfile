FROM composer:2.3.4 AS backend
ADD https://github.com/mlocati/docker-php-extension-installer/releases/download/1.5.29/install-php-extensions /usr/local/bin/
RUN chmod +x /usr/local/bin/install-php-extensions && \
    install-php-extensions gd intl
COPY . /var/app
WORKDIR /var/app
ARG RELEASE_VERSION
ENV RELEASE_VERSION=$RELEASE_VERSION
RUN composer install --optimize-autoloader --no-dev && composer dump-version

FROM node:14.18.3-alpine AS release
ARG RELEASE_FILENAME
ARG RELEASE_VERSION
ENV RELEASE_FILENAME=$RELEASE_FILENAME
ENV RELEASE_VERSION=$RELEASE_VERSION
COPY --from=backend /var/app /var/app
WORKDIR /var/app/src/frontend
RUN npm install -g npm@7 && npm install && npm run release
WORKDIR /var/app/
RUN find *.tar.gz -type f -exec sh -c "eval sha1sum {} > {}.sha1" \;

FROM scratch
COPY --from=release /var/app/*.tar.gz .
COPY --from=release /var/app/*.sha1 .
