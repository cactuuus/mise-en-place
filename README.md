# Mise En Place

## Prerequisites

- Docker
- Git

## Docker Installation

Install Docker Engine following
the [official Ubuntu installation guide](https://docs.docker.com/engine/install/ubuntu/).
Add your user to the docker group for proper permissions:

```bash
sudo usermod -aG docker $USER
newgrp docker # refresh group
``` 

## Development Setup (Sail)

```bash
# Copy environment file
cp .env.example .env

# Install Composer dependencies
sudo docker run --rm \  
	-u "$(id -u):$(id -g)" \  
	-v "$(pwd):/var/www/html" \  
	-w /var/www/html \  
	laravelsail/php82-composer:latest \  
	composer install --ignore-platform-reqs

# Create Sail alias
alias sail='bash vendor/bin/sail'

# Start containers in the background
sail up -d

# Complete application setup 
sail composer install 
sail artisan key:generate  
sail artisan migrate # use --seed for db seeders
php artisan optimize

# Install and compile frontend assets
sail npm install 
sail npm run dev  
```

### Troubleshooting Sail

If you encounter database connection issues, update your `.env`:

```env
DB_HOST=mysql
```

To completely reset Docker containers:

```bash
sail down --volumes
sail up --build -d
```

Access MySQL prompt:

```bash
sail artisan db
```

## Production/Staging Setup (Laradock)

For production and staging deployments, this project uses a modified Laradock configuration.

### Initial Setup

```bash
# (Start in app's root folder)

# initialise submodules
git submodule update --init

# Copy application environment 
cp .env.example .env 
# Edit .env with your production settings

# Configure Laradock 
cd laradock 
cp .env.example .env 
# Edit laradock/.env with your settings

# Apply GD extension patch for image processing 
git apply ../laradock-gd-php84-fix.patch

# make sure you have one .env file for laradock too, if not run:
cp .env.example .env

# Create database initialization script 
# Make sure your credentials match the ones setup in your env files.
cat  > mysql/docker-entrypoint-initdb.d/createdb.sql <<  'EOF' 
CREATE DATABASE IF NOT EXISTS `mise` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
 CREATE USER IF NOT EXISTS 'mise'@'%' IDENTIFIED BY 'your_password'; 
GRANT ALL PRIVILEGES ON `mise`.* TO 'mise'@'%'; 
FLUSH PRIVILEGES; 
EOF

# Configure queue worker
cp php-worker/supervisord.d/laravel-worker.conf.example php-worker/supervisord.d/laravel-worker.conf

# start the app
docker compose up -d nginx mysql workspace php-worker

# perform usual initial setup
docker compose  exec workspace bash 
# Inside workspace: 
composer install 
php artisan key:generate 
php artisan migrate 
php artisan storage:link
php artisan optimize
```

## Manual deployment:

```bash
# (Start in app's root folder)

# Get latest updates
git fetch origin
git pull origin
git submodule update --recursive

# Restart containers
cd laradock
docker compose down
docker compose build
docker compose up -d nginx mysql workspace php-worker

# Update application 
docker compose exec workspace bash
# Inside workspace: 
composer  install --no-dev --optimize-autoloader 
php artisan migrate --force 
php artisan cache:clear 
php artisan config:clear 
php artisan route:clear 
php artisan view:clear
php artisan optimize
```

## Patch notes

#### Supported Image Formats:

- ✅ JPEG (photos)
- ✅ PNG (graphics, screenshots)
- ✅ GIF (animations)
- ✅ WebP (modern web format)
- ✅ BMP (Windows bitmap)

### What the patch fixes:

- PHP 8.4 GD configuration for modern image formats
- WebP and JPEG processing in queue workers
- Required for Spatie Media Library image conversions

## Common Commands

```bash
# View logs
docker compose logs nginx
docker compose logs mysql
docker compose logs workspace

# Access containers
docker compose exec workspace bash
docker compose exec mysql mysql -u root -p

# Restart specific services
docker compose restart nginx
docker compose restart php-worker

# Check container status
docker compose ps
```

## Troubleshooting

### MySQL Connection Issues

- Ensure MySQL container is fully started before running migrations
- Check that database credentials match between Laravel and Laradock `.env` files
- Verify the database user has proper permissions

### Image Processing Issues

- Ensure the GD patch has been applied
- Check that `php-worker` container has the GD extension installed

### Permission Issues

- Check file permissions in storage directory
- Ensure web server can write to `storage/` and `bootstrap/cache/`
- Run `php artisan storage:link` if public storage access fails
