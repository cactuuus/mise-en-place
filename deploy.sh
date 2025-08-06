#!/bin/bash

# Simple Staging Deployment Script for Mise En Place
# Usage: ./deploy.sh

set -e  # Exit on any error

echo "🚀 Starting deployment..."

# Colors for output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${GREEN}✓${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}⚠${NC} $1"
}

print_error() {
    echo -e "${RED}✗${NC} $1"
}

# Check if we're in the right directory
if [ ! -f "artisan" ]; then
    print_error "artisan file not found. Make sure you're in the Laravel project root."
    exit 1
fi

if [ ! -d "laradock" ]; then
    print_error "laradock directory not found. Make sure Laradock is properly set up."
    exit 1
fi

# 1. Fetch and pull latest changes
print_status "Fetching latest changes from Git..."
git fetch origin
CURRENT_BRANCH=$(git rev-parse --abbrev-ref HEAD)
print_status "Current branch: $CURRENT_BRANCH"
git pull origin $CURRENT_BRANCH

# 2. Update submodules (in case Laradock got updated)
print_status "Updating Git submodules..."
git submodule update --recursive

# 3. Put application in maintenance mode
print_status "Putting application in maintenance mode..."
cd laradock
if docker compose ps | grep -q "workspace.*Up"; then
    docker compose exec workspace php artisan down || print_warning "Could not put app in maintenance mode (maybe it's already down)"
else
    print_warning "Workspace container not running, skipping maintenance mode"
fi

# 4. Stop containers
print_status "Stopping Docker containers..."
docker compose down

# 5. Build containers (with cache for speed)
print_status "Building Docker containers..."
docker compose up -d nginx mysql workspace php-worker

# 6. Start containers
print_status "Starting Docker containers..."
docker compose up -d nginx mysql workspace php-worker

# 7. Wait for MySQL to be ready
print_status "Waiting for MySQL to be ready..."
sleep 10
for i in {1..30}; do
    if docker compose exec mysql mysql -u root -pstaging -e "SELECT 1" >/dev/null 2>&1; then
        print_status "MySQL is ready!"
        break
    fi
    if [ $i -eq 30 ]; then
        print_error "MySQL failed to start after 30 attempts"
        exit 1
    fi
    echo "Waiting for MySQL... ($i/30)"
    sleep 2
done

# 8. Install/update Composer dependencies
print_status "Installing Composer dependencies..."
docker compose exec workspace git config --global --add safe.directory /var/www
APP_ENV=$(docker compose exec workspace grep -E '^APP_ENV=' /var/www/.env | cut -d '=' -f 2)
if [ "${APP_ENV}" = "production" ]; then
    print_status "Detected APP_ENV=production. Installing production dependencies..."
    docker compose exec workspace composer install --no-dev --optimize-autoloader
else
    print_status "Detected APP_ENV=${APP_ENV}. Installing development dependencies..."
    docker compose exec workspace composer install --optimize-autoloader
fi

# 9. Run database migrations
print_status "Running database migrations..."
docker compose exec workspace php artisan migrate --force

# 10. Clear all existing caches first
print_status "Clearing existing caches..."
docker compose exec workspace php artisan cache:clear
docker compose exec workspace php artisan config:clear
docker compose exec workspace php artisan route:clear
docker compose exec workspace php artisan view:clear

# 11. Optimize application (rebuild all caches)
print_status "Optimizing application..."
docker compose exec workspace php artisan optimize

# 12. Ensure storage is properly linked
print_status "Ensuring storage link exists..."
docker compose exec workspace php artisan storage:link || print_warning "Storage link already exists or failed"

# 13. Set proper permissions (if needed)
print_status "Setting proper permissions..."
# Fix permissions using php-fpm container as root
docker compose exec --user=root php-fpm chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
docker compose exec --user=root php-fpm chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# 14. Take application out of maintenance mode
print_status "Taking application out of maintenance mode..."
docker compose exec workspace php artisan up

# 15. Show final status
print_status "Checking application status..."
if docker compose ps | grep -q "nginx.*Up" && docker compose ps | grep -q "mysql.*Up"; then
    print_status "🎉 Deployment completed successfully!"
    echo
    echo "Application should be available at your configured domain."
    echo "Container status:"
    docker compose ps
else
    print_error "Some containers may not be running properly"
    docker compose ps
    exit 1
fi

echo
print_status "Deployment finished at $(date)"
