FROM osixia/phpldapadmin

ENV PHPLDAPADMIN_HTTPS false
ENV PHPLDAPADMIN_TRUST_PROXY_SSL true
ENV PHPLDAPADMIN_LDAP_CLIENT_TLS false
ADD templates /var/www/phpldapadmin_bootstrap/templates
ADD environment/env.yaml /container/environment/01-custom/env.yaml

VOLUME ["/var/www/phpldapadmin"]
