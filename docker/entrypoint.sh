#!/bin/sh
set -eu

cd /var/www/html

PORT="${PORT:-8080}"

printf 'Listen 0.0.0.0:%s\n' "$PORT" > /etc/apache2/ports.conf
sed -i "s/__PORT__/${PORT}/g" /etc/apache2/sites-available/000-default.conf

mkdir -p \
    storage/app/public \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    bootstrap/cache

chown -R www-data:www-data \
    storage/app/public \
    storage/framework \
    bootstrap/cache

chmod -R ug+rwX \
    storage/app/public \
    storage/framework \
    bootstrap/cache

if [ ! -e public/storage ]; then
    php artisan storage:link
elif [ -L public/storage ]; then
    echo "public/storage already linked."
else
    echo "public/storage exists and is not a symlink; leaving it unchanged."
fi

php artisan optimize:clear
php artisan optimize

rm -f \
    /etc/apache2/mods-enabled/mpm_event.load \
    /etc/apache2/mods-enabled/mpm_event.conf \
    /etc/apache2/mods-enabled/mpm_worker.load \
    /etc/apache2/mods-enabled/mpm_worker.conf

a2enmod mpm_prefork rewrite headers >/dev/null

MPM_COUNT="$(find /etc/apache2/mods-enabled -maxdepth 1 -name 'mpm_*.load' | wc -l)"
test "$MPM_COUNT" -eq 1
test -e /etc/apache2/mods-enabled/mpm_prefork.load

find /etc/apache2/mods-enabled -maxdepth 1 -name 'mpm_*.load' -print
apache2ctl configtest
apache2ctl -M 2>&1 | grep -E 'mpm_.*_module' | grep 'mpm_prefork_module'
if apache2ctl -M 2>&1 | grep -E 'mpm_(event|worker)_module'; then
    echo "Invalid Apache MPM enabled." >&2
    exit 1
fi

exec "$@"
