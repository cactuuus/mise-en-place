FROM jkaninda/laravel-php-fpm:8.4-alpine AS base

ENV COMPOSER_ALLOW_SUPERUSER=1

# Install missing extensions including GD with image libraries
RUN apk add --no-cache --virtual .build-deps \
    $PHPIZE_DEPS \
    icu-dev \
    libzip-dev \
    jpeg-dev \
    libpng-dev \
    libwebp-dev \
    freetype-dev \
    && docker-php-ext-configure gd --with-jpeg --with-webp --with-freetype \
    && docker-php-ext-install intl zip exif gd \
    && apk del .build-deps \
    && apk add --no-cache icu libzip jpeg libpng libwebp freetype npm mariadb-client mariadb-connector-c

WORKDIR /var/www/html

# Staging dependencies stage
FROM base AS staging-deps
COPY composer.json composer.lock ./
RUN composer install --no-scripts --optimize-autoloader --no-interaction --prefer-dist

# Production dependencies stage
FROM base AS production-deps
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --optimize-autoloader --no-interaction --prefer-dist

# Asset building stage
FROM node:22-alpine AS assets
WORKDIR /var/www/html
COPY package*.json ./
RUN npm ci
COPY resources/ resources/
COPY vite.config.js ./
RUN npm run build && npm cache clean --force

# Final staging image
FROM base AS staging
WORKDIR /var/www/html
COPY --from=staging-deps /var/www/html/vendor /var/www/html/vendor
COPY --from=assets /var/www/html/public/build ./public/build
COPY . .
RUN composer run-script post-autoload-dump \
    && rm -rf node_modules package*.json vite.config.js resources/js resources/css
USER www-data
EXPOSE 9000
CMD ["php-fpm"]

# Final production image
FROM base AS production
WORKDIR /var/www/html
COPY --from=production-deps /var/www/html/vendor /var/www/html/vendor
COPY --from=assets /var/www/html/public/build ./public/build
COPY . .
RUN composer run-script post-autoload-dump \
    && rm -rf node_modules package*.json vite.config.js resources/js resources/css
USER www-data
EXPOSE 9000
CMD ["php-fpm"]
