# syntax=docker/dockerfile:1.7

##
# Multi-stage Dockerfile to build and deploy the Laravel 12 backend
# with all required PHP extensions (intl, zip, exif, etc.),
# compiled frontend assets, and cached configuration.
##

ARG PHP_BASE_IMAGE=php:8.2-apache-bookworm
ARG BUN_IMAGE=oven/bun:1.1.34
ARG COMPOSER_IMAGE=composer:2.7

FROM ${COMPOSER_IMAGE} AS composer

# -----------------------------------------------------------------------------
# Shared PHP+Apache base with all required extensions and Composer.
# -----------------------------------------------------------------------------
FROM ${PHP_BASE_IMAGE} AS php-base

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public

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
    && docker-php-ext-install -j$(nproc) \
        intl \
        zip \
        exif \
        gd \
        pcntl \
        bcmath \
        pdo_mysql \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && a2enmod rewrite headers env \
    && sed -ri "s!/var/www/html!${APACHE_DOCUMENT_ROOT}!g" /etc/apache2/sites-available/000-default.conf \
        /etc/apache2/sites-available/default-ssl.conf \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

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
    LOG_CHANNEL=stderr

WORKDIR /var/www/html

# Copy application code
COPY --chown=www-data:www-data . .

# Bring in pre-installed vendors and built assets
COPY --from=vendor /var/www/html/vendor ./vendor
COPY --from=assets /app/public/build ./public/build

# Ensure storage & bootstrap/cache writable
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R ug+rw storage bootstrap/cache

# Optimize Laravel caches
RUN php artisan config:cache \
    && php artisan route:cache \
    && php artisan event:cache \
    && php artisan view:cache

EXPOSE 80

CMD ["apache2-foreground"]

