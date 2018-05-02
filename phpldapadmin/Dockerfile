FROM osixia/phpldapadmin

ENV PHPLDAPADMIN_HTTPS false
ENV PHPLDAPADMIN_TRUST_PROXY_SSL true
ENV PHPLDAPADMIN_LDAP_CLIENT_TLS false

ADD templates /var/www/phpldapadmin/templates
ADD config /var/www/phpldapadmin/config

VOLUME ["/var/www/phpldapadmin"]
