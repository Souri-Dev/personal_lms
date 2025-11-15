#!/bin/sh
set -e

echo "=== Starting CSIT Dept LMS Application ==="

# Set environment
export APP_ENV=prod
export APP_DEBUG=0

# Create directories
mkdir -p var/cache var/log public/uploads
chmod -R 777 var public/uploads

# Install dependencies if vendor doesn't exist
if [ ! -d "vendor" ]; then
    echo "Installing dependencies..."
    composer install --no-dev --optimize-autoloader --no-interaction
fi

# Wait for database
if [ -n "$DATABASE_URL" ]; then
    echo "Waiting for database connection..."
    timeout=60
    while [ $timeout -gt 0 ]; do
        if php bin/console dbal:run-sql "SELECT 1" > /dev/null 2>&1; then
            echo "✓ Database connected"
            break
        fi
        echo "Waiting for database... ($timeout seconds left)"
        sleep 2
        timeout=$((timeout-2))
    done
    
    # Run migrations
    echo "Running database migrations..."
    php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration || echo "Migration skipped"
fi

# Clear and warm cache
echo "Preparing cache..."
rm -rf var/cache/prod
php bin/console cache:clear --env=prod --no-warmup
php bin/console cache:warmup --env=prod

# Fix permissions
chmod -R 777 var

echo "=== Application ready ==="

# Start PHP-FPM and Nginx
php-fpm -D
nginx -g "daemon off;"
