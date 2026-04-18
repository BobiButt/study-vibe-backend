#!/bin/bash

# Install dependencies
composer install --no-interaction --no-dev --optimize-autoloader

# Generate key if not exists
php artisan key:generate --no-interaction

# Run migrations (if you have database)
php artisan migrate --force

# Start Laravel server
php artisan serve --host=0.0.0.0 --port=8080
