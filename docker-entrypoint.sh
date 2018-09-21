#!/bin/sh
set -euo pipefail
chown -R apache:apache /var/www
  
if [[ "${FETCH}" == "yes" || ! -e /var/www/localhost/htdocs/artisan ]]; then
  cp -Rp /root/htdocs/* /var/www/localhost/htdocs
  cp -Rp /root/htdocs/.[^.]* /var/www/localhost/htdocs
fi

if mysqlshow --host=${DB_HOST} --user=${DB_USERNAME} --password=${DB_PASSWORD} ${DB_DATABASE} users; then
  echo "database ready!"
else
  php artisan migrate:refresh
  php artisan passport:install
fi

if [[ "${INIT}" == "yes" ]]; then
#  php artisan voyager:install
  echo -e "yes\nyes\nyes\n" | php artisan migrate:refresh
  echo -e "0" | php artisan vendor:publish
  php artisan -q make:auth
  php artisan config:clear
  php artisan view:clear
  php artisan cache:clear
  php artisan opcache:clear

  if [[ "${MAIL}" != "your@mail.addr" ]]; then
    sed -ri -e "s/^(\s*ServerAdmin).*$/\1 ${MAIL}/g" /etc/apache2/httpd.conf
#    echo -e "${MAIL}\n${WEB_PASSWORD}\n" | php artisan voyager:admin ${MAIL} --create
  fi
fi

rm -f /run/apache2/httpd.pid
exec httpd -DFOREGROUND
