FROM osixia/phpldapadmin

ADD functions.php /var/www/ldapadmin_bootstrap/lib/functions.php
ADD templates /var/www/phpldapadmin_bootstrap/templates
ADD environment /container/environment/01-custom
ADD config.php /container/service/phpldapadmin/assets/config/config.php

VOLUME ["/var/www/phpldapadmin"]
