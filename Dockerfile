# syntax=docker/dockerfile:1.7

##
# Multi-stage Dockerfile to build and deploy the Laravel 12 backend
# with all required PHP extensions (intl, zip, exif, etc.),
# compiled frontend assets, and cached configuration.
##

ARG PHP_BASE_IMAGE=dunglas/frankenphp:php8.2.29-bookworm
ARG BUN_IMAGE=oven/bun:1.1.34
ARG COMPOSER_IMAGE=composer:2.7

FROM ${COMPOSER_IMAGE} AS composer

# -----------------------------------------------------------------------------
# Shared PHP base with all required extensions + Composer available.
# -----------------------------------------------------------------------------
FROM ${PHP_BASE_IMAGE} AS php-base

RUN apt-get update && apt-get install -y --no-install-recommends \
        git \
        unzip \
        libicu-dev \
        libzip-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
        libwebp-dev \
        libfreetype6-dev \
        libxml2-dev \
        libonig-dev \
    && install-php-extensions \
        intl \
        zip \
        exif \
        gd \
        pcntl \
        bcmath \
        opcache \
        redis \
        pdo_mysql \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer /usr/bin/composer /usr/bin/composer

WORKDIR /app

# -----------------------------------------------------------------------------
# Composer dependencies (cached separately for faster rebuilds)
# -----------------------------------------------------------------------------
FROM php-base AS vendor

COPY composer.json composer.lock ./

RUN composer install \
    --no-dev \
    --no-interaction \
    --no-progress \
    --prefer-dist \
    --optimize-autoloader \
    --no-scripts

# -----------------------------------------------------------------------------
# Frontend/Vite assets (Bun)
# -----------------------------------------------------------------------------
FROM ${BUN_IMAGE} AS assets
WORKDIR /app

COPY package.json bun.lock ./
RUN bun install --frozen-lockfile

COPY resources ./resources
COPY vite.config.js package-lock.json ./

RUN bun run build

# -----------------------------------------------------------------------------
# Runtime image
# -----------------------------------------------------------------------------
FROM php-base AS runtime

ENV APP_ENV=production \
    APP_DEBUG=false \
    LOG_CHANNEL=stderr \
    PORT=8080

# Copy application code
COPY --chown=www-data:www-data . .

# Bring in pre-installed vendors and built assets
COPY --from=vendor /app/vendor ./vendor
COPY --from=assets /app/public/build ./public/build

# Ensure storage & bootstrap/cache writable
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R ug+rw storage bootstrap/cache

# Optimize Laravel caches
RUN php artisan config:cache \
    && php artisan route:cache \
    && php artisan event:cache \
    && php artisan view:cache

EXPOSE 8080

CMD ["frankenphp", "run", "--config=/app/Caddyfile"]

