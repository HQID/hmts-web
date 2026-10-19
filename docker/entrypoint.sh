#!/bin/bash
set -e

# 1. Setup Railway Port & Nginx Listen Directives
PORT=${PORT:-8080}
echo "==> Configuring Nginx to listen on port ${PORT} (and fallback port 80)..."

if [ "$PORT" != "80" ]; then
    LISTEN_DIRECTIVES="listen ${PORT}; listen 80;"
else
    LISTEN_DIRECTIVES="listen 80;"
fi

sed "s|__LISTEN_DIRECTIVES__|${LISTEN_DIRECTIVES}|g" /etc/nginx/nginx.conf.template > /etc/nginx/nginx.conf

# 2. Ensure runtime, storage and bootstrap directories exist with proper permissions
mkdir -p /run /run/nginx /var/log/nginx /var/log/supervisor
mkdir -p /var/www/html/storage/framework/{sessions,views,cache}
mkdir -p /var/www/html/storage/logs
mkdir -p /var/www/html/bootstrap/cache

chown -R www-data:www-data /run /var/log/nginx /var/log/supervisor
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# 3. Create storage symlink
if [ ! -L /var/www/html/public/storage ]; then
    echo "==> Creating storage symlink..."
    php artisan storage:link || true
fi

# 4. Check & run Database Migrations
if [ -n "$DB_HOST" ] || [ -n "$MYSQLHOST" ] || [ -n "$DB_URL" ] || [ -n "$MYSQL_URL" ]; then
    if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
        echo "==> Running database migrations..."
        max_retries=6
        count=0
        until php artisan migrate --force || [ $count -ge $max_retries ]; do
            count=$((count+1))
            echo "==> Waiting for database connection (attempt $count of $max_retries)..."
            sleep 3
        done

        # Optional seeder execution
        if [ "${RUN_SEEDER:-false}" = "true" ]; then
            echo "==> Seeding database (RUN_SEEDER=true)..."
            php artisan db:seed --force || echo "==> Seeder notice: finished or skipped."
        fi
    fi
fi

# 5. Production cache optimization
if [ "${APP_ENV:-production}" = "production" ]; then
    echo "==> Optimizing configuration, route, and view caches..."
    php artisan config:cache || true
    php artisan route:cache || true
    php artisan view:cache || true
fi

echo "==> Application ready. Launching Nginx and PHP-FPM on port ${PORT}..."
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
