FROM php:7.4-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    zip \
    unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        pdo_mysql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
        opcache \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/backend

# Copy backend source
COPY backend/ .

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts \
    && rm -f bootstrap/cache/services.php bootstrap/cache/packages.php

# Set permissions
RUN chown -R www-data:www-data /var/www/backend/storage \
    && chown -R www-data:www-data /var/www/backend/bootstrap/cache \
    && chmod -R 775 /var/www/backend/storage \
    && chmod -R 775 /var/www/backend/bootstrap/cache

EXPOSE 9000
CMD ["php-fpm"]
