#!/bin/sh
set -e

# When ./backend is bind-mounted over /var/www/backend, directories and
# permissions baked into the image are shadowed by the host folder. Ensure the
# runtime-writable paths exist and are owned by www-data (php-fpm) on every boot.
# review-form-media holds user-uploaded review images/docs/logos and is
# git-ignored, so it won't exist on a fresh deploy unless created here.
mkdir -p \
    /var/www/backend/public/review-form-media \
    /var/www/backend/storage \
    /var/www/backend/bootstrap/cache

chown -R www-data:www-data \
    /var/www/backend/public/review-form-media \
    /var/www/backend/storage \
    /var/www/backend/bootstrap/cache

chmod -R 775 \
    /var/www/backend/public/review-form-media \
    /var/www/backend/storage \
    /var/www/backend/bootstrap/cache

exec "$@"
