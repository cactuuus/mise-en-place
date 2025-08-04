############################################
# Base Image
############################################

# Learn more about the Server Side Up PHP Docker Images at:
# https://serversideup.net/open-source/docker-php/
FROM serversideup/php:8.4-fpm-nginx-alpine AS base

## Install additional PHP extensions needed for Laravel + Filament
USER root
RUN install-php-extensions exif intl gd imagick

############################################
# Development Image
############################################
FROM base AS development

# We can pass USER_ID and GROUP_ID as build arguments
# to ensure the www-data user has the same UID and GID
# as the user running Docker.
ARG USER_ID
ARG GROUP_ID

# Switch to root so we can set the user ID and group ID
USER root

# Set the user ID and group ID for www-data
RUN docker-php-serversideup-set-id www-data $USER_ID:$GROUP_ID  && \
    docker-php-serversideup-set-file-permissions --owner $USER_ID:$GROUP_ID --service nginx

# Drop privileges back to www-data
USER www-data

############################################
# CI image
############################################
FROM base AS ci

# Sometimes CI images need to run as root
# so we set the ROOT user and configure
# the PHP-FPM pool to run as www-data
USER root
RUN echo "user = www-data" >> /usr/local/etc/php-fpm.d/docker-php-serversideup-pool.conf && \
    echo "group = www-data" >> /usr/local/etc/php-fpm.d/docker-php-serversideup-pool.conf

############################################
# Production Image
############################################
FROM base AS deploy

# Copy application files
COPY --chown=www-data:www-data . /var/www/html

# Install composer dependencies
USER root
RUN cd /var/www/html && \
    composer install --no-dev --optimize-autoloader --no-scripts --no-interaction && \
    composer clear-cache

# Create necessary directories and set permissions
RUN mkdir -p /var/www/html/.infrastructure/volume_data/sqlite/ && \
    mkdir -p /var/www/html/storage/{app,logs,framework/{cache,sessions,views}} && \
    chown -R www-data:www-data /var/www/html/.infrastructure && \
    chown -R www-data:www-data /var/www/html/storage && \
    chmod -R 755 /var/www/html/.infrastructure && \
    chmod -R 755 /var/www/html/storage

# Switch back to www-data user
USER www-data
