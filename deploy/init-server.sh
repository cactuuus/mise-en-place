#!/bin/bash
source ../server/print_utils.sh
source config.env
set -e

APP_ENV=${1}
if [[ "$APP_ENV" != "staging" && "$APP_ENV" != "production" ]]; then
    echo "Usage: $0 [staging|production]"
    exit 1
fi

# Set paths dynamically
if [[ "$APP_ENV" == "staging" ]]; then
    APP_PATH=$STAGING_PATH
    ENV_FILE=".env.staging"
else
    APP_PATH=$PRODUCTION_PATH
    ENV_FILE=".env.production"
fi

# Check if folder exists on remote
if ssh ${SERVER_USER}@${SERVER_HOST} "[ -d ${APP_PATH} ]"; then
    print_warning "WARNING: ${APP_PATH} already exists on ${SERVER_HOST}"
    read -p "Do you want to continue and overwrite files? (y/n): " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        echo "Aborted."
        exit 0
    fi
fi

# Ensure target path exists
ssh ${SERVER_USER}@${SERVER_HOST} "mkdir -p ${APP_PATH}"

# Copy over deployment scripts & correct env file
scp ../server/in-server-deploy.sh \
    ../server/docker-compose.yml \
    ../server/print_utils.sh \
    ../server/nginx.template.conf \
    ../server/${ENV_FILE} \
    ${SERVER_USER}@${SERVER_HOST}:${APP_PATH}/

# Rename env file on server as plain `.env`
ssh ${SERVER_USER}@${SERVER_HOST} "mv ${APP_PATH}/${ENV_FILE} ${APP_PATH}/.env"

echo
print_status "Files copied to ${APP_PATH} successfully."
