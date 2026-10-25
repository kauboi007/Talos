FROM php:8.5-cli-alpine AS php-build

RUN apk add --no-cache \
        icu-libs \
        libzip \
        oniguruma \
    && apk add --no-cache --virtual .build-deps \
        $PHPIZE_DEPS \
        icu-dev \
        curl-dev \
        libzip-dev \
        oniguruma-dev \
        openssl-dev \
    && docker-php-ext-install \
        bcmath intl pcntl zip \
    && pecl install mongodb redis \
    && docker-php-ext-enable mongodb redis \
    && apk del .build-deps

FROM php-build AS composer

RUN apk add --no-cache curl \
    && curl -fsSL https://getcomposer.org/download/latest-stable/composer.phar \
        -o /usr/local/bin/composer \
    && chmod +x /usr/local/bin/composer

FROM php-build AS application-build

WORKDIR /var/www/html

COPY --from=composer /usr/local/bin/composer /usr/local/bin/composer
COPY composer.json composer.lock ./
RUN composer install \
        --no-dev \
        --no-interaction \
        --no-progress \
        --prefer-dist \
        --optimize-autoloader \
        --no-scripts

COPY . .
RUN composer dump-autoload --no-dev --classmap-authoritative

FROM alpine:3.21 AS assets

ARG TAILWIND_VERSION=4.3.3

RUN apk add --no-cache ca-certificates curl libstdc++ \
    && curl -fsSL \
        "https://github.com/tailwindlabs/tailwindcss/releases/download/v${TAILWIND_VERSION}/tailwindcss-linux-x64-musl" \
        -o /usr/local/bin/tailwindcss \
    && chmod +x /usr/local/bin/tailwindcss

WORKDIR /var/www/html
COPY --from=application-build /var/www/html/resources ./resources
COPY --from=application-build /var/www/html/vendor ./vendor
RUN mkdir -p public/css \
    && tailwindcss -i resources/css/app.css -o public/css/app.css --minify

FROM php-build AS runtime

WORKDIR /var/www/html

ENV APP_ENV=production \
    APP_DEBUG=false \
    LOG_CHANNEL=stderr

COPY --from=application-build /var/www/html ./
COPY --from=assets /var/www/html/public/css/app.css ./public/css/app.css
COPY docker/entrypoint.sh /usr/local/bin/talos-entrypoint

RUN chmod +x /usr/local/bin/talos-entrypoint \
    && mkdir -p storage/framework/cache storage/framework/sessions \
        storage/framework/views bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache

EXPOSE 8000

ENTRYPOINT ["talos-entrypoint"]