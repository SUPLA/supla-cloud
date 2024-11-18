FROM composer:2.8.2 AS backend
COPY . /var/app
WORKDIR /var/app
ARG RELEASE_VERSION
ENV RELEASE_VERSION=$RELEASE_VERSION
RUN composer install --optimize-autoloader --ignore-platform-req=ext-gd --ignore-platform-req=ext-intl --no-dev && composer dump-version

FROM node:16.20.2-alpine AS frontend
ARG RELEASE_FILENAME
ARG RELEASE_VERSION
ENV RELEASE_FILENAME=$RELEASE_FILENAME
ENV RELEASE_VERSION=$RELEASE_VERSION
COPY --from=backend /var/app /var/app
WORKDIR /var/app/src/frontend
RUN npm install -g npm@7 && npm install && npm run build && npm run release
WORKDIR /var/app/
RUN find *.tar.gz -type f -exec sh -c "eval sha1sum {} > {}.sha1" \;

FROM scratch
COPY --from=frontend /var/app/*.tar.gz .
COPY --from=frontend /var/app/*.sha1 .
