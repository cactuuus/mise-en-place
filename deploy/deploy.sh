#!/bin/bash
# deploy/deploy.sh - Simplified deployment script
set -e

# Get script directory and load config
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
source "$SCRIPT_DIR/config.env"

# Colors for output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
BLUE='\033[0;34m'
NC='\033[0m'

print_status() { echo -e "${GREEN}✓${NC} $1"; }
print_warning() { echo -e "${YELLOW}⚠${NC} $1"; }
print_error() { echo -e "${RED}✗${NC} $1"; }
print_info() { echo -e "${BLUE}ℹ${NC} $1"; }

# Parse arguments
BRANCH_OR_ENV=$1

if [ -z "$BRANCH_OR_ENV" ]; then
    print_error "Usage:"
    echo "  $0 production           # Deploy main branch to production"
    echo "  $0 [branch-name]        # Deploy any branch to staging"
    exit 1
fi

# Determine environment and settings
if [ "$BRANCH_OR_ENV" = "production" ]; then
    ENVIRONMENT="production"
    BRANCH="main"
    APP_PATH="$PRODUCTION_PATH"
    DOMAIN="$PRODUCTION_DOMAIN"
    INSTALL_DEV="$PRODUCTION_INSTALL_DEV"

    print_warning "🚨 PRODUCTION DEPLOYMENT 🚨"
    print_info "This will deploy main branch to production"
    read -p "Are you sure? (y/N): " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        print_info "Deployment cancelled"
        exit 0
    fi
else
    ENVIRONMENT="staging"
    BRANCH="$BRANCH_OR_ENV"
    APP_PATH="$STAGING_PATH"
    DOMAIN="$STAGING_DOMAIN"
    INSTALL_DEV="$STAGING_INSTALL_DEV"
fi

print_status "Deploying to $ENVIRONMENT..."
print_info "Environment: $ENVIRONMENT"
print_info "Branch: $BRANCH"
print_info "Domain: $DOMAIN"

# Generate image tag
COMMIT_HASH=$(git rev-parse --short HEAD)
IMAGE_TAG="${ENVIRONMENT}-${COMMIT_HASH}"

# Deploy to server
print_status "Connecting to server..."
ssh ${SERVER_USER}@${SERVER_HOST} << EOF
set -e

# Colors for remote
GREEN='\033[0;32m'
RED='\033[0;31m'
NC='\033[0m'
print_remote() { echo -e "\${GREEN}[SERVER]\${NC} \$1"; }

print_remote "Starting deployment..."

# Ensure app directory exists
if [ ! -d "$APP_PATH" ]; then
    print_remote "Cloning repository..."
    mkdir -p $APP_PATH
    git clone $REPO_URL $APP_PATH
fi

cd $APP_PATH

# Check .env exists
if [ ! -f .env ]; then
    echo "❌ .env not found at $APP_PATH/.env"
    echo "Please copy .env.example and configure it first"
    exit 1
fi

# Git operations
print_remote "Fetching and checking out $BRANCH..."
git fetch origin
git checkout $BRANCH
git pull origin $BRANCH

CURRENT_COMMIT=\$(git rev-parse --short HEAD)
print_remote "Current commit: \$CURRENT_COMMIT"

# Build Docker image
print_remote "Building image: mise-$ENVIRONMENT:$IMAGE_TAG"
docker build -t mise-$ENVIRONMENT:$IMAGE_TAG -f deploy/Dockerfile .

# Update docker-compose.server.yml with environment-specific values
print_remote "Configuring docker-compose.server.yml..."
sed -i.bak \
  -e "s/mise-ENVIRONMENT/mise-$ENVIRONMENT/g" \
  -e "s/ENVIRONMENT-mise/$ENVIRONMENT-mise/g" \
  -e "s/DOMAIN_PLACEHOLDER/$DOMAIN/g" \
  -e "s/:latest/:$IMAGE_TAG/g" \
  docker-compose.server.yml

# Install dependencies
print_remote "Installing composer dependencies..."
if [ "$INSTALL_DEV" = "true" ]; then
    docker run --rm -v \$PWD:/var/www/html -w /var/www/html mise-$ENVIRONMENT:$IMAGE_TAG composer install --optimize-autoloader
else
    docker run --rm -v \$PWD:/var/www/html -w /var/www/html mise-$ENVIRONMENT:$IMAGE_TAG composer install --optimize-autoloader --no-dev
fi

print_remote "Building assets..."
docker run --rm -v \$PWD:/var/www/html -w /var/www/html mise-$ENVIRONMENT:$IMAGE_TAG npm ci
docker run --rm -v \$PWD:/var/www/html -w /var/www/html mise-$ENVIRONMENT:$IMAGE_TAG npm run build

# Database migrations
print_remote "Running migrations..."
docker run --rm --env-file .env --network shared_db_network -v \$PWD:/var/www/html -w /var/www/html mise-$ENVIRONMENT:$IMAGE_TAG php artisan migrate --force

# Laravel optimization
print_remote "Optimizing Laravel..."
docker run --rm --env-file .env -v \$PWD:/var/www/html -w /var/www/html mise-$ENVIRONMENT:$IMAGE_TAG bash -c "
    php artisan cache:clear &&
    php artisan config:clear &&
    php artisan route:clear &&
    php artisan view:clear &&
    php artisan optimize
"

# Deploy containers
print_remote "Starting containers..."
docker-compose -f docker-compose.server.yml down
docker-compose -f docker-compose.server.yml up -d

# Health check
print_remote "Health check..."
sleep 10
if docker-compose -f docker-compose.server.yml ps | grep -q "Up"; then
    print_remote "✅ Deployment successful!"
    docker-compose -f docker-compose.server.yml ps
else
    print_remote "❌ Container startup failed"
    docker-compose -f docker-compose.server.yml logs
    exit 1
fi

print_remote "🎉 Deployed $ENVIRONMENT ($BRANCH) to $DOMAIN"
EOF

print_status "🎉 Deployment completed!"
print_info "Access your app at: https://$DOMAIN"
