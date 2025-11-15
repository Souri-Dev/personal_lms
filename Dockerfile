# Use PHP-FPM base image
FROM php:8.2-fpm

# Install system dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    zip \
    libicu-dev \
    libzip-dev \
    libonig-dev \
    nginx \
    supervisor \
    && docker-php-ext-install \
    intl \
    pdo \
    pdo_mysql \
    zip \
    opcache \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy PHP configuration
COPY docker/php.ini $PHP_INI_DIR/conf.d/custom.ini

WORKDIR /var/www/html

# Copy application code
COPY . .

# Install PHP dependencies (will be done in start.sh to avoid build issues)
# RUN composer install --no-dev --optimize-autoloader --no-scripts --no-interaction --prefer-dist

# Create necessary directories and set permissions
RUN mkdir -p var/cache var/log \
    && chown -R www-data:www-data var public \
    && chmod -R 775 var

# Copy nginx and supervisor configs
COPY docker/nginx.conf /etc/nginx/sites-available/default
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Copy start script
COPY start.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh

# Set proper permissions for cache and log directories
RUN mkdir -p var/cache var/log public/uploads \
    && chown -R www-data:www-data var public \
    && chmod -R 775 var

EXPOSE 80

CMD ["/usr/local/bin/start.sh"]
