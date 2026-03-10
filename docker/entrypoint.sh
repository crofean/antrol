#!/bin/sh

set -e

# Create log directories
mkdir -p /var/log/supervisor
mkdir -p /var/log/nginx

# Generate APP_KEY if not set
if [ -z "$APP_KEY" ]; then
    echo "Generating APP_KEY..."
    php /app/artisan key:generate
fi

# Run migrations if MIGRATE_ON_START is set
if [ "$MIGRATE_ON_START" = "true" ]; then
    echo "Running migrations..."
    php /app/artisan migrate --force
fi

# Run cache commands
echo "Clearing and caching configuration..."
php /app/artisan config:cache
php /app/artisan route:cache
php /app/artisan view:cache

echo "Starting supervisor..."
exec /usr/bin/supervisord -c /etc/supervisor/supervisord.conf
