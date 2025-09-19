# Stage 1: The Build Stage
FROM php:8.3-fpm-alpine AS builder

# Install system dependencies, including build dependencies
RUN apk add --no-cache \
    git curl zip unzip nodejs npm \
    libpng-dev libjpeg-turbo-dev libwebp-dev freetype-dev \
    oniguruma-dev libxml2-dev libzip-dev \
    imagemagick-dev libmemcached-dev icu-dev \
    zlib-dev \
    $PHPIZE_DEPS

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql mysqli mbstring exif pcntl bcmath gd zip intl opcache

# Install PECL extensions
RUN pecl install redis imagick memcached \
    && docker-php-ext-enable redis imagick memcached

# Remove build dependencies
RUN apk del .build-deps

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory and copy application files for dependency installation
WORKDIR /var/www/html
COPY . /var/www/html

# Run composer and npm installs
RUN composer install --no-dev --optimize-autoloader
RUN npm install && npm run build


# Stage 2: The Final Runtime Image
FROM php:8.3-fpm-alpine

# Install only the necessary runtime dependencies
RUN apk add --no-cache \
    libpng libjpeg-turbo libwebp freetype \
    oniguruma libxml2 libzip \
    imagemagick libmemcached icu-data-full \
    zlib \
    redis \
    imagick \
    memcached

# Copy PHP extensions from the builder stage
COPY --from=builder /usr/local/lib/php/extensions/no-debug-non-zts-20230831/ /usr/local/lib/php/extensions/no-debug-non-zts-20230831/
COPY --from=builder /usr/local/etc/php/conf.d/ /usr/local/etc/php/conf.d/

# Copy Composer and the application code from the builder stage
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY --from=builder /var/www/html /var/www/html

WORKDIR /var/www/html
USER www-data
EXPOSE 9000
CMD ["php-fpm"]
