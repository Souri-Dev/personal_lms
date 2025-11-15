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

# Copy composer files (symfony.lock is optional)
COPY composer.json ./
COPY composer.lock ./

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

# Copy start script
COPY start.sh /usr/local/bin/start.sh
RUN chmod +x /usr/local/bin/start.sh

# Set proper permissions for cache and log directories
RUN mkdir -p var/cache var/log public/uploads \
    && chown -R www-data:www-data var public \
    && chmod -R 775 var

EXPOSE 80

CMD ["/usr/local/bin/start.sh"]
