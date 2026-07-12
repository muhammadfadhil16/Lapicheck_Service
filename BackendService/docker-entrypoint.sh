#!/bin/sh
set -e

if [ "${AUTO_MIGRATE:-false}" = "true" ]; then
    php artisan migrate --force
    php artisan db:seed --force
fi

rm -rf public/storage
php artisan storage:link || true
a2dismod mpm_event mpm_worker 2>/dev/null || true
a2enmod mpm_prefork
exec apache2-foreground
