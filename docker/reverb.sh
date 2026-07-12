#!/bin/sh
set -eu

cd /var/www/html

PORT="${PORT:-8080}"

php artisan config:clear

exec php artisan reverb:start --host=0.0.0.0 --port="${PORT}"
