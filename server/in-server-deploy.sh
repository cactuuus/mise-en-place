#!/bin/bash
source print_utils.sh
source .env
set -e

print_info "Pulling latest '$APP_ENV' image from GitHub registry"
echo $GITHUB_TOKEN | docker login ghcr.io -u $GITHUB_USERNAME --password-stdin
docker compose -f docker-compose.server.yml pull

print_info "Starting containers"
docker compose -f docker-compose.server.yml up -d

print_info "Setting up permissions"
docker compose exec --user root app chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

print_info "Performing migrations"
docker compose -f docker-compose.server.yml exec app php artisan migrate --force

print_info "Performing optimizations"
docker compose -f docker-compose.server.yml exec app php artisan optimize

print_info "Cleaning up old images"
docker image prune -f

print_status "Deployment successfull"
