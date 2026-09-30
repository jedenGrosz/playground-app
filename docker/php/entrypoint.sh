#!/bin/sh
set -eu

if [ ! -f .env ]; then
    cp .env.example .env
fi

composer install --no-interaction --prefer-dist

if grep -q '^APP_KEY=$' .env; then
    php artisan key:generate --force
fi

mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

php artisan migrate --force
php artisan db:seed --force
php artisan dummyjson:sync

exec "$@"
