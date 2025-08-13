# Stage 1: Base image with PHP and Composer
FROM php:8.2-cli AS base

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    zip \
    libicu-dev \
    libpq-dev \
    libzip-dev \
    libonig-dev \
    && docker-php-ext-install \
    intl \
    pdo \
    pdo_mysql \
    zip \
    && docker-php-ext-enable intl

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Install Symfony CLI
RUN curl -sS https://get.symfony.com/cli/installer | bash && \
    mv /root/.symfony*/bin/symfony /usr/local/bin/symfony

WORKDIR /var/www/html

# Stage 2: Install dependencies
FROM base AS deps
COPY composer.json composer.lock ./
RUN composer install --no-scripts --no-autoloader --prefer-dist --no-interaction

# Stage 3: Final app image
FROM base AS final
WORKDIR /var/www/html

COPY --from=deps /var/www/html/vendor /var/www/html/vendor
COPY . .
COPY .env ./

# Fix permissions
RUN mkdir -p var/cache var/log && chown -R www-data:www-data var

EXPOSE 8000
CMD ["symfony", "serve", "--no-tls", "--allow-http", "--port=8000", "--ansi"]
