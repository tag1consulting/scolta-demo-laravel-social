#!/bin/bash
# Post-start hook for MyStream demo site
set -e

# Install PHP dependencies
ddev composer install --no-interaction

# Run DB import if dump exists, otherwise run fresh migrations
if [ -f db/dump.sql.gz ]; then
    ddev import-db --file=db/dump.sql.gz
    ddev exec php artisan migrate --force
else
    ddev exec php artisan migrate --force
fi

ddev exec php artisan config:cache
ddev exec php artisan route:cache
