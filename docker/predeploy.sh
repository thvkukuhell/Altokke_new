#!/bin/sh
set -eu

cd /var/www/html

php artisan migrate --force
