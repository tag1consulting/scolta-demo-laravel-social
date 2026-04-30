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

# Workaround for scolta v0.3.5 asset bundling bug (remove after v0.3.6)
# See: https://github.com/tag1consulting/scolta-laravel/issues/XX
if [ -f vendor/tag1/scolta-php/assets/js/scolta.js ]; then
    ddev exec mkdir -p vendor/tag1/scolta-laravel/public/js
    ddev exec mkdir -p vendor/tag1/scolta-laravel/public/css
    ddev exec cp vendor/tag1/scolta-php/assets/js/scolta.js vendor/tag1/scolta-laravel/public/js/
    ddev exec cp vendor/tag1/scolta-php/assets/css/scolta.css vendor/tag1/scolta-laravel/public/css/ 2>/dev/null || true
fi
