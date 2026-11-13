#!/bin/sh
set -eu

cd /var/www/html
mkdir -p storage/app/public storage/app/private storage/framework/cache/data \
    storage/framework/sessions storage/framework/views storage/logs bootstrap/cache
chown www-data:www-data storage storage/app storage/app/public storage/app/private
chown -R www-data:www-data storage/framework storage/logs bootstrap/cache

case "${1:-}" in
    apache2-foreground)
        if [ -z "${APP_KEY:-}" ]; then
            echo 'Set APP_KEY in .env before starting the application.' >&2
            exit 1
        fi
        # Cache only after production environment variables are available.
        php artisan config:cache
        php artisan route:cache
        php artisan view:cache
        chown -R www-data:www-data bootstrap/cache storage/framework
        ;;
esac

# Scheduler jobs run as the same user as web requests so shared cache locks work.
if [ "${1:-}" = "php" ]; then
    exec gosu www-data "$@"
fi
exec docker-php-entrypoint "$@"
