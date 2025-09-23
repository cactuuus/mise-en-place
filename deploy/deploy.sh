#!/bin/bash
source ../server/print_utils.sh
source config.env
set -e

print_status "Building image locally..."

APP_ENV=${1}
IMAGE_NAME=mise-$APP_ENV
IMAGE_TAG=$(git rev-parse --short HEAD)

if [[ "$APP_ENV" != "staging" && "$APP_ENV" != "production" ]]; then
    echo "Usage: $0 [staging|production]"
    exit 1
fi

if [ "$APP_ENV" = "production" ]; then
    APP_PATH="$PRODUCTION_PATH"
    DOMAIN="$PRODUCTION_DOMAIN"

    print_warning "🚨 PRODUCTION DEPLOYMENT 🚨"
    print_info "This will deploy main branch to production"
    read -p "Are you sure? (y/N): " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        print_info "Deployment cancelled"
        exit 0
    fi
else
    APP_PATH="$STAGING_PATH"
    DOMAIN="$STAGING_DOMAIN"
fi

print_status "Building $APP_ENV image locally..."
docker build --build-arg APP_ENV=$APP_ENV -t ghcr.io/${GITHUB_USERNAME}/${IMAGE_NAME}:${IMAGE_TAG} ../
docker tag ghcr.io/${GITHUB_USERNAME}/${IMAGE_NAME}:${IMAGE_TAG} ghcr.io/${GITHUB_USERNAME}/${IMAGE_NAME}:latest

print_status "Pushing to GitHub Container Registry..."
echo $GITHUB_TOKEN | docker login ghcr.io -u ${GITHUB_USERNAME} --password-stdin
docker push ghcr.io/${GITHUB_USERNAME}/${IMAGE_NAME}:${IMAGE_TAG}
docker push ghcr.io/${GITHUB_USERNAME}/${IMAGE_NAME}:latest

print_status "Deploying $ENV to server..."
ssh ${SERVER_USER}@${SERVER_HOST} "cd ${APP_PATH} && ./in-server-deploy.sh"
