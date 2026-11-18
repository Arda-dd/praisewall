# Builds the admin assets and the embed widget.
FROM node:24-alpine AS assets
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci --no-audit --no-fund
COPY vite.config.js vite.widget.config.js tsconfig.json ./
COPY resources resources
COPY public public
RUN npm run build

FROM serversideup/php:8.4-fpm-nginx

ENV APP_ENV=production \
    APP_DEBUG=false \
    LOG_CHANNEL=stderr \
    DB_CONNECTION=sqlite \
    DB_DATABASE=/var/www/html/storage/database/database.sqlite \
    SESSION_DRIVER=file \
    CACHE_STORE=file \
    PHP_OPCACHE_ENABLE=1 \
    PHP_UPLOAD_MAX_FILE_SIZE=4M \
    PHP_POST_MAX_SIZE=8M

COPY --chown=www-data:www-data . /var/www/html
RUN composer install --no-dev --no-interaction --no-progress --optimize-autoloader \
    && rm -rf /var/www/html/storage/database /var/www/html/database/database.sqlite
COPY --from=assets --chown=www-data:www-data /app/public/build /var/www/html/public/build
COPY --from=assets --chown=www-data:www-data /app/public/widget.js /var/www/html/public/widget.js
COPY --chmod=755 docker/entrypoint.sh /etc/entrypoint.d/50-praisewall.sh

# Database, uploads and logs live here.
VOLUME /var/www/html/storage
