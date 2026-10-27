FROM php:7.4-fpm

RUN sed -i \
    -e 's|deb http://deb.debian.org/debian bullseye main|deb [check-valid-until=no] http://snapshot.debian.org/archive/debian/20221114T000000Z bullseye main|' \
    -e 's|deb http://deb.debian.org/debian-security bullseye-security main|deb [check-valid-until=no] http://snapshot.debian.org/archive/debian-security/20221114T000000Z bullseye-security main|' \
    -e 's|deb http://deb.debian.org/debian bullseye-updates main|deb [check-valid-until=no] http://snapshot.debian.org/archive/debian/20221114T000000Z bullseye-updates main|' \
    /etc/apt/sources.list

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
# review-form-media holds user-uploaded review images/docs/logos and must be
# writable by php-fpm (www-data); it is git-ignored so it won't exist otherwise.
RUN mkdir -p /var/www/backend/public/review-form-media \
    && chown -R www-data:www-data /var/www/backend/storage \
    && chown -R www-data:www-data /var/www/backend/bootstrap/cache \
    && chown -R www-data:www-data /var/www/backend/public/review-form-media \
    && chmod -R 775 /var/www/backend/storage \
    && chmod -R 775 /var/www/backend/bootstrap/cache \
    && chmod -R 775 /var/www/backend/public/review-form-media

# Upload/post size limits (see docker/php/uploads.ini)
COPY docker/php/uploads.ini /usr/local/etc/php/conf.d/uploads.ini

# Entrypoint fixes writable-dir permissions at runtime (needed because the host
# ./backend is bind-mounted over the image contents in docker-compose).
COPY docker/php/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 9000
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["php-fpm"]
