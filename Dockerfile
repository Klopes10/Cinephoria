FROM php:8.2-fpm

# Installe des extensions nécessaires
RUN apt-get update && apt-get install -y \
    git unzip zip libpq-dev libonig-dev libxml2-dev curl libssl-dev pkg-config \
    && pecl install mongodb-1.15.0 \
    && docker-php-ext-enable mongodb \
    && docker-php-ext-install pdo pdo_pgsql

# Corrige Git
RUN git config --global --add safe.directory /var/www/html

# Installe Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .

RUN composer install
