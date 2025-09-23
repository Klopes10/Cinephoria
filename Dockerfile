FROM php:8.2-fpm

# Système + dépendances de compilation (ICU pour intl, g++ pour la compile)
RUN set -eux; \
    apt-get update; \
    apt-get install -y --no-install-recommends \
        git unzip zip curl pkg-config \
        libssl-dev libxml2-dev \
        libpq-dev \
        libicu-dev g++ \
        libonig-dev \
    ; \
    # Extensions PHP natives
    docker-php-ext-configure intl; \
    docker-php-ext-install -j"$(nproc)" intl pdo pdo_pgsql; \
    # PECL MongoDB
    pecl install mongodb-1.15.0; \
    docker-php-ext-enable mongodb; \
    # Nettoyage
    rm -rf /var/lib/apt/lists/*



# Corrige Git (contexte Docker)
RUN git config --global --add safe.directory /var/www/html

# Installe Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY . .

ARG TRUSTED_HOSTS="^.*$"
ENV TRUSTED_HOSTS=${TRUSTED_HOSTS}
# Installe les dépendances PHP (ajoute --no-dev --optimize-autoloader en prod)
RUN composer install --no-scripts
