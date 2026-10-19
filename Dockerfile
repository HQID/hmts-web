# ==============================================================================
# Stage 1: Build frontend assets with Node.js
# ==============================================================================
FROM node:22-alpine AS frontend
WORKDIR /app

# Cache package dependencies
COPY package.json package-lock.json ./
RUN npm ci

# Copy full application code to build Vite bundle
COPY . .
RUN npm run build

# ==============================================================================
# Stage 2: Production PHP 8.4 runtime with Nginx and PHP-FPM
# ==============================================================================
FROM php:8.4-fpm-alpine AS app

# Install system dependencies
RUN apk add --no-cache \
    nginx \
    supervisor \
    bash \
    curl \
    git \
    unzip \
    libzip-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    icu-dev

# Install required PHP extensions
ADD --chmod=0755 https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN install-php-extensions \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip \
    intl \
    opcache

# Install Composer binary from official image
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy application files
COPY . .

# Copy compiled frontend assets from Stage 1
COPY --from=frontend /app/public/build ./public/build

# Install production PHP dependencies
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN cp -n .env.example .env || true && \
    composer install --no-dev --optimize-autoloader --no-interaction --no-progress

# Copy configuration files
COPY docker/nginx.conf.template /etc/nginx/nginx.conf.template
COPY docker/php.ini /usr/local/etc/php/conf.d/custom.ini
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh

# Remove CRLF if present and mark entrypoint executable
RUN sed -i -e 's/\r$//' /usr/local/bin/entrypoint.sh && \
    chmod +x /usr/local/bin/entrypoint.sh

# Create runtime directories and set proper permissions
RUN mkdir -p /run/nginx /var/log/nginx /var/log/supervisor && \
    mkdir -p storage/framework/{sessions,views,cache} storage/logs bootstrap/cache && \
    chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache && \
    chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 80

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
