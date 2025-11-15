# Stage 1: Build dependencies
FROM php:8.2-fpm AS builder

# Install system dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    zip \
    libicu-dev \
    libzip-dev \
    libonig-dev \
    && docker-php-ext-install \
    intl \
    pdo \
    pdo_mysql \
    zip \
    opcache \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy composer files
COPY composer.json composer.lock symfony.lock* ./

# Install PHP dependencies (production only, optimized)
RUN composer install --no-dev --optimize-autoloader --no-scripts --no-interaction --prefer-dist

# Stage 2: Final production image
FROM php:8.2-fpm

# Install runtime dependencies
RUN apt-get update && apt-get install -y \
    libicu-dev \
    libzip-dev \
    nginx \
    supervisor \
    && docker-php-ext-install \
    intl \
    pdo \
    pdo_mysql \
    zip \
    opcache \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Copy PHP configuration
COPY docker/php.ini $PHP_INI_DIR/conf.d/custom.ini

WORKDIR /var/www/html

# Copy vendor from builder stage
COPY --from=builder /var/www/html/vendor ./vendor

# Copy application code
COPY . .

# Create necessary directories and set permissions
RUN mkdir -p var/cache var/log \
    && chown -R www-data:www-data var public \
    && chmod -R 775 var

# Copy nginx and supervisor configs
COPY docker/nginx.conf /etc/nginx/sites-available/default
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Create entrypoint script
RUN echo '#!/bin/sh\n\
set -e\n\
\n\
# Wait for database to be ready\n\
until php bin/console dbal:run-sql "SELECT 1" > /dev/null 2>&1; do\n\
  echo "Waiting for database..."\n\
  sleep 2\n\
done\n\
\n\
# Run migrations\n\
php bin/console doctrine:migrations:migrate --no-interaction\n\
\n\
# Clear and warm up cache\n\
php bin/console cache:clear --env=prod\n\
php bin/console cache:warmup --env=prod\n\
\n\
# Start supervisord\n\
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf\n\
' > /usr/local/bin/docker-entrypoint.sh \
    && chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 80

ENTRYPOINT ["/usr/local/bin/docker-entrypoint.sh"]
