#!/bin/sh
set -eu

cd /var/www/html

php artisan config:clear

exec php artisan queue:work --sleep=3 --tries=3 --timeout=90
