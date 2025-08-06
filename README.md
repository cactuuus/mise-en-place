### Mise En Place

## UT Setup and installation notes

## Software requirements ##

Install docker engine (Ubuntu installation steps https://docs.docker.com/engine/install/ubuntu/)

## Project setup ##

Run the following commands:

    sudo usermod -aG docker $USER

    # Installs composer which gives you access to the vendor folder as well as sail
    sudo docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php82-composer:latest \
    composer install --ignore-platform-reqs

    # Define the alias for Sail
    alias sail='bash vendor/bin/sail'

    # Start the application containers in the background
    sail up -d

    # Install the composer dependencies
    sail composer install

    # Generate a key for the application
    sail artisan key:generate

    # Run the database migrations
    sail artisan migrate --seed

If you have an error, then on some systems you need to go to the .env file and change the DB_HOST:

    DB_HOST=mysql

If the database keeps failing on docker for some reason (you might have tried to create the image when another container
was running on the same port) then you can try to remove the container and image and try again:

    docker-compose down --volumes
    sail up --build

You can access the mysql db prompt if needed here:

    sail artisan db

Once the database is running, you can set up the app with the following commands:

    # Compile the frontend assets
    sail npm install
    sail npm run dev

When you are done, you can stop the containers with the following command:

    sail down

To start the app again, use the following command:

    sail up -d
    sail npm run dev

## Laradock Setup

This project uses a modified Laradock configuration for comprehensive image format support in its queue worker.

### Supported Image Formats:

- ✅ JPEG (photos)
- ✅ PNG (graphics, screenshots)
- ✅ GIF (animations)
- ✅ WebP (modern web format)
- ✅ BMP (Windows bitmap)

### Development Setup:

1. Clone the repository
2. Initialize submodules: `git submodule update --init`
3. Apply GD fix: `cd laradock && git apply ../laradock-gd-php84-fix.patch`
4. Build containers: `docker-compose build php-worker`
5. Start services: `docker-compose up -d nginx mysql workspace php-worker`

### What the patch fixes:

- PHP 8.4 GD configuration for modern image formats
- WebP and JPEG processing in queue workers
- Required for Spatie Media Library image conversions

