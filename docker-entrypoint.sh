#!/bin/sh

# Make sure we stop on errors
set -e

# Cache configurations to optimize Laravel speed on production
echo "Caching configurations..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Run database migrations
echo "Running database migrations..."
php artisan migrate --force

# Seed database (default credentials)
echo "Seeding default data..."
php artisan db:seed --force

# Run storage link
echo "Linking storage..."
if [ ! -d "public/storage" ]; then
    php artisan storage:link --force
fi

# Execute the passed CMD (Apache)
echo "Starting web server..."
exec "$@"
