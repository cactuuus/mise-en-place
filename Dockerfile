# Stage 1: The Build Stage
FROM php:8.3-fpm-alpine AS builder

# Install system dependencies, including build dependencies
RUN apk add --no-cache \
    git curl zip unzip nodejs npm \
    libpng-dev libjpeg-turbo-dev libwebp-dev freetype-dev \
    oniguruma-dev libxml2-dev libzip-dev \
    imagemagick-dev libmemcached-dev icu-dev \
    $PHPIZE_DEPS

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql mysqli mbstring exif pcntl bcmath gd zip intl opcache

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
    imagemagick libmemcached icu-data-full

# Install PHP extensions (from sources, without build-deps)
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp \
    && docker-php-ext-install -j$(nproc) \
        pdo_mysql mysqli mbstring exif pcntl bcmath gd zip intl opcache

# Copy Composer and the application code from the builder stage
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY --from=builder /var/www/html /var/www/html

WORKDIR /var/www/html
USER www-data
EXPOSE 9000
CMD ["php-fpm"]
