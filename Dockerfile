############################################
# Base Image
############################################
FROM serversideup/php:8.4-fpm-nginx-alpine AS base

# Install PHP extensions
USER root
RUN install-php-extensions exif intl gd imagick

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

# Create a startup script that fixes permissions at runtime
RUN echo '#!/bin/sh' > /usr/local/bin/fix-permissions.sh && \
    echo 'echo "🔧 Fixing permissions for mounted volumes..."' >> /usr/local/bin/fix-permissions.sh && \
    echo 'mkdir -p /var/www/html/.infrastructure/volume_data/sqlite' >> /usr/local/bin/fix-permissions.sh && \
    echo 'mkdir -p /var/www/html/storage/app/private' >> /usr/local/bin/fix-permissions.sh && \
    echo 'mkdir -p /var/www/html/storage/app/public' >> /usr/local/bin/fix-permissions.sh && \
    echo 'mkdir -p /var/www/html/storage/logs' >> /usr/local/bin/fix-permissions.sh && \
    echo 'mkdir -p /var/www/html/storage/framework/cache' >> /usr/local/bin/fix-permissions.sh && \
    echo 'mkdir -p /var/www/html/storage/framework/sessions' >> /usr/local/bin/fix-permissions.sh && \
    echo 'mkdir -p /var/www/html/storage/framework/views' >> /usr/local/bin/fix-permissions.sh && \
    echo 'chown -R www-data:www-data /var/www/html/.infrastructure' >> /usr/local/bin/fix-permissions.sh && \
    echo 'chown -R www-data:www-data /var/www/html/storage' >> /usr/local/bin/fix-permissions.sh && \
    echo 'chmod -R 755 /var/www/html/.infrastructure' >> /usr/local/bin/fix-permissions.sh && \
    echo 'chmod -R 755 /var/www/html/storage' >> /usr/local/bin/fix-permissions.sh && \
    echo 'if [ ! -f /var/www/html/.infrastructure/volume_data/sqlite/database.sqlite ]; then' >> /usr/local/bin/fix-permissions.sh && \
    echo '    touch /var/www/html/.infrastructure/volume_data/sqlite/database.sqlite' >> /usr/local/bin/fix-permissions.sh && \
    echo 'fi' >> /usr/local/bin/fix-permissions.sh && \
    echo 'chown www-data:www-data /var/www/html/.infrastructure/volume_data/sqlite/database.sqlite' >> /usr/local/bin/fix-permissions.sh && \
    echo 'chmod 664 /var/www/html/.infrastructure/volume_data/sqlite/database.sqlite' >> /usr/local/bin/fix-permissions.sh && \
    echo 'echo "✅ Permissions fixed"' >> /usr/local/bin/fix-permissions.sh && \
    chmod +x /usr/local/bin/fix-permissions.sh

# Create a wrapper script that runs permission fix before the main entrypoint
RUN echo '#!/bin/sh' > /usr/local/bin/startup-wrapper.sh && \
    echo 'set -e' >> /usr/local/bin/startup-wrapper.sh && \
    echo 'echo "🚀 Starting with permission fixes..."' >> /usr/local/bin/startup-wrapper.sh && \
    echo '/usr/local/bin/fix-permissions.sh' >> /usr/local/bin/startup-wrapper.sh && \
    echo 'echo "🔄 Switching to www-data user..."' >> /usr/local/bin/startup-wrapper.sh && \
    echo 'exec su-exec www-data docker-php-serversideup-entrypoint "$@"' >> /usr/local/bin/startup-wrapper.sh && \
    chmod +x /usr/local/bin/startup-wrapper.sh

# Use our wrapper as the entrypoint
ENTRYPOINT ["/usr/local/bin/startup-wrapper.sh"]
CMD ["supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
