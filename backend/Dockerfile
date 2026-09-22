FROM php:7.4-fpm

RUN sed -i \
    -e 's|deb http://deb.debian.org/debian bullseye main|deb [check-valid-until=no] http://snapshot.debian.org/archive/debian/20221114T000000Z bullseye main|' \
    -e 's|deb http://deb.debian.org/debian-security bullseye-security main|deb [check-valid-until=no] http://snapshot.debian.org/archive/debian-security/20221114T000000Z bullseye-security main|' \
    -e 's|deb http://deb.debian.org/debian bullseye-updates main|deb [check-valid-until=no] http://snapshot.debian.org/archive/debian/20221114T000000Z bullseye-updates main|' \
    /etc/apt/sources.list

RUN apt-get update && apt-get install -y \
    git \
    curl \
    unzip \
    zip \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev

RUN docker-php-ext-install \
    pdo \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/backend

COPY . .

RUN composer install --no-dev --optimize-autoloader

RUN chown -R www-data:www-data storage bootstrap/cache

EXPOSE 9000

CMD ["php-fpm"]