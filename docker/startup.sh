#!/bin/sh
set -e

cd /var/www

# ── 1. Inject PORT into nginx config (Railway sets $PORT dynamically)
export PORT="${PORT:-80}"
echo "==> Binding nginx to port $PORT"
envsubst '${PORT}' < /etc/nginx/http.d/default.conf > /tmp/nginx_default.conf
cp /tmp/nginx_default.conf /etc/nginx/http.d/default.conf

# ── 2. Generate APP_KEY if not provided
if [ -z "$APP_KEY" ]; then
    echo "==> Generating APP_KEY..."
    php artisan key:generate --force
fi

# ── 3. Start nginx + php-fpm immediately (so health check can pass)
#       Run migrations in background after services are up
(
    echo "==> Waiting for services to start..."
    sleep 5

    echo "==> Waiting for database..."
    RETRIES=0
    until php artisan migrate:status > /dev/null 2>&1; do
        RETRIES=$((RETRIES + 1))
        if [ $RETRIES -ge 15 ]; then
            echo "==> ERROR: Database not reachable after 45s, skipping migrations"
            break
        fi
        echo "    DB not ready, retrying ($RETRIES/15)..."
        sleep 3
    done

    echo "==> Running migrations..."
    php artisan migrate --force || echo "WARNING: Migrations failed"

    echo "==> Creating storage symlink..."
    php artisan storage:link 2>/dev/null || true

    echo "==> Optimizing..."
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache

    echo "==> Setup complete!"
) &

# ── 4. Launch supervisor (nginx + php-fpm) in foreground
echo "==> Launching nginx and php-fpm..."
exec /usr/bin/supervisord -n -c /etc/supervisor/conf.d/supervisord.conf
