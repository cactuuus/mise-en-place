FROM jkaninda/laravel-php-fpm:8.4-alpine

ARG APP_ENV=staging
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
    && apk add --no-cache icu libzip jpeg libpng libwebp freetype npm

# Copy Laravel project files
COPY . /var/www/html
WORKDIR /var/www/html

# Install dependencies based on environment
RUN if [ "$APP_ENV" = "staging" ]; then \
      composer install --optimize-autoloader --no-interaction --prefer-dist; \
    else \
      composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist; \
    fi \
    && if [ -f "package.json" ]; then npm ci && npm run build && npm cache clean --force; fi \
    && rm -rf node_modules package*.json vite.config.js resources/js resources/css

USER www-data
EXPOSE 9000
CMD ["php-fpm"]
