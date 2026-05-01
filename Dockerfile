FROM php:8.2-cli-alpine

# Dépendances système
RUN apk add --no-cache \
    curl zip unzip git \
    mysql-client \
    busybox-extras \
    sqlite-dev \
    libpq-dev         # requis pour pdo_pgsql (Render PostgreSQL)

# Extensions PHP
# pdo_sqlite → tests PHPUnit (DB_DATABASE=:memory:)
# pdo_pgsql  → déploiement Render (PostgreSQL free tier)
RUN docker-php-ext-install pdo_mysql pdo_sqlite pdo_pgsql bcmath pcntl

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Copier d'abord les fichiers de dépendances (cache Docker optimisé)
COPY composer.json composer.lock ./
RUN composer install \
    --no-interaction \
    --no-scripts \
    --no-autoloader \
    --prefer-dist

# Copier le reste de l'application
COPY . .

# Créer un .env minimal pour le build (sera écrasé au démarrage)
RUN cp .env.example .env 2>/dev/null || touch .env

# Finaliser l'autoloader
RUN composer dump-autoload --optimize

# Permissions Laravel
RUN chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Entrypoint
COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 8000

ENTRYPOINT ["/entrypoint.sh"]
