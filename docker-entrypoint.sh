#!/bin/sh
set -e

# Run Laravel database migrations automatically during container startup
echo "Running database migrations..."
php artisan migrate --force

# Execute the container's main command (e.g. apache2-foreground)
exec "$@"
