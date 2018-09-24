#!/bin/sh
set -euo pipefail
chown -R apache:apache /var/www
  
if mysqlshow --host=${DB_HOST} --user=${DB_USERNAME} --password=${DB_PASSWORD} ${DB_DATABASE} users; then
  echo "database ready!"
else
  php artisan migrate:refresh
  php artisan passport:install
fi

composer update
php artisan cache:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

rm -f /run/apache2/httpd.pid
exec httpd -DFOREGROUND
