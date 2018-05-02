FROM osixia/phpldapadmin

ENV PHPLDAPADMIN_HTTPS false
ENV PHPLDAPADMIN_TRUST_PROXY_SSL true
ENV PHPLDAPADMIN_LDAP_CLIENT_TLS false

RUN cp -R /var/www/phpldapadmin_bootstrap/*.* /var/www/phpldapadmin

ADD templates /var/www/phpldapadmin/templates
ADD config /var/www/phpldapadmin/config

VOLUME ["/var/www/phpldapadmin"]
