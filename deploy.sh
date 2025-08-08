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

# Read MySQL password from laradock/.env
print_status "Reading configuration..."
MYSQL_PASSWORD=$(grep -E '^MYSQL_ROOT_PASSWORD=' laradock/.env | cut -d '=' -f 2 | tr -d '\r\n')
if [ -z "$MYSQL_PASSWORD" ]; then
    print_warning "MYSQL_ROOT_PASSWORD not found in laradock/.env, using default 'root'"
    MYSQL_PASSWORD="root"
fi

# 1. Fetch and pull latest changes
print_status "Fetching latest changes from Git..."
git fetch origin
CURRENT_BRANCH=$(git rev-parse --abbrev-ref HEAD)
print_status "Current branch: $CURRENT_BRANCH"
git pull origin $CURRENT_BRANCH

# 2. Update submodules
print_status "Updating Git submodules..."
git submodule update --recursive

# 3. Stop all containers
print_status "Stopping Docker containers..."
cd laradock
docker compose down

# 4. Start MySQL and workspace for build tasks
print_status "Starting MySQL and workspace for build tasks..."
docker compose up -d mysql workspace

# 5. Wait for MySQL to be ready
print_status "Waiting for MySQL to be ready..."
sleep 5
for i in {1..30}; do
    if docker compose exec mysql mysqladmin ping -u root -p"$MYSQL_PASSWORD" --silent >/dev/null 2>&1; then
        print_status "MySQL is ready!"
        break
    fi
    if [ $i -eq 30 ]; then
        print_error "MySQL failed to start after 30 attempts"
        print_status "Checking MySQL logs..."
        docker compose logs mysql | tail -20
        exit 1
    fi
    echo "Waiting for MySQL... ($i/30)"
    sleep 2
done

# 6. Configure git in workspace
print_status "Configuring workspace..."
docker compose exec workspace git config --global --add safe.directory /var/www

# 7. Get APP_ENV to determine dependency installation
APP_ENV=$(docker compose exec workspace grep -E '^APP_ENV=' /var/www/.env | cut -d '=' -f 2 | tr -d '\r\n')
if [ -z "$APP_ENV" ]; then
    APP_ENV="local"
    print_warning "APP_ENV not found, defaulting to 'local'"
fi

# 8. Install Composer dependencies in workspace
print_status "Installing Composer dependencies..."
if [ "${APP_ENV}" = "production" ]; then
    print_status "Detected APP_ENV=production. Installing production dependencies..."
    docker compose exec workspace composer install --no-dev --optimize-autoloader
else
    print_status "Detected APP_ENV=${APP_ENV}. Installing development dependencies..."
    docker compose exec workspace composer install --optimize-autoloader
fi

# 9. Build assets in workspace
print_status "Installing NPM dependencies and building assets..."
docker compose exec workspace npm install
docker compose exec workspace npm run build

# 10. Run database migrations in workspace
print_status "Running database migrations..."
docker compose exec workspace php artisan migrate --force

# 11. Start production containers and stop workspace
print_status "Starting production containers..."
docker compose up -d nginx php-fpm php-worker
print_status "Stopping workspace container..."
docker compose stop workspace

# 12. Set proper permissions before optimization
print_status "Setting proper permissions..."
docker compose exec --user=root php-fpm chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache
docker compose exec --user=root php-fpm chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# 13. Clear caches and optimize in php-fpm
print_status "Clearing existing caches..."
docker compose exec php-fpm php artisan cache:clear
docker compose exec php-fpm php artisan config:clear
docker compose exec php-fpm php artisan route:clear
docker compose exec php-fpm php artisan view:clear

print_status "Optimizing application..."
docker compose exec php-fpm php artisan optimize

# 14. Ensure storage link exists
print_status "Ensuring storage link exists..."
docker compose exec php-fpm php artisan storage:link || print_warning "Storage link already exists or failed"

# 15. Final health check
print_status "Performing health check..."
sleep 3
if docker compose ps | grep -q "nginx.*Up" && docker compose ps | grep -q "mysql.*Up" && docker compose ps | grep -q "php-fpm.*Up"; then
    print_status "🎉 All containers are running!"

    if docker compose exec php-fpm php artisan about >/dev/null 2>&1; then
        print_status "✅ Laravel application is healthy"
    else
        print_warning "⚠️  Laravel application may have issues"
    fi

    echo
    echo "🎉 Deployment completed successfully!"
    echo "Container status:"
    docker compose ps
else
    print_error "Some containers may not be running properly"
    docker compose ps
    exit 1
fi

echo
print_status "Deployment finished at $(date)"
