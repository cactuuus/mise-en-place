# start with lightweight image
FROM php:8.3-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    git curl zip unzip nodejs npm \
    libpng-dev libjpeg-turbo-dev libwebp-dev freetype-dev \
    oniguruma-dev libxml2-dev libzip-dev \
    imagemagick-dev libmemcached-dev icu-dev

# Install build dependencies temporarily
RUN apk add --no-cache --virtual .build-deps \
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

# Create user
RUN addgroup -g 1000 www-data && adduser -u 1000 -G www-data -s /bin/sh -D www-data

WORKDIR /var/www/html
USER www-data
EXPOSE 9000
CMD ["php-fpm"]
