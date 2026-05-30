FROM php:8.3-fpm

RUN apt-get update && apt-get install -y \
    unzip \
    git \
    curl \
    libzip-dev \
    && docker-php-ext-install zip \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .

RUN composer install --no-dev --optimize-autoloader 2>/dev/null || true

RUN mkdir -p /var/www/html/var/rate_limit \
    && chown -R www-data:www-data /var/www/html/var \
    && chmod -R 775 /var/www/html/var
