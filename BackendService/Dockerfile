FROM php:8.4-apache AS base

RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    unzip \
    git \
    && docker-php-ext-install pdo_mysql zip bcmath

RUN a2enmod rewrite

ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

WORKDIR /var/www/html

FROM composer:2 AS composer-stage
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --no-progress --optimize-autoloader --no-scripts

FROM base AS final
COPY --from=composer-stage /app/vendor ./vendor
COPY . .

RUN php artisan package:discover --ansi || true \
    && rm -rf public/storage \
    && php artisan storage:link \
    && chown -R www-data:www-data storage bootstrap/cache public/storage \
    && chmod -R 775 storage bootstrap/cache

CMD ["sh", "-c", "a2dismod mpm_event mpm_worker 2>/dev/null || true; a2enmod mpm_prefork; apache2-foreground"]