FROM leejoneshane/laravel

ENV DOMAIN ldap.tp.edu.tw
ENV MAIL your@gmail.com
ENV WEB_PASSWORD password
ENV TZ Asia/Taipei
ENV DB_HOST 163.21.249.80
ENV DB_PORT 3306
ENV DB_DATABASE laravel
ENV DB_USERNAME root
ENV DB_PASSWORD password
ENV LDAP_HOST ldaps://ldap.tp.edu.tw
ENV LDAP_ROOTDN cn=admin,dc=tp,dc=edu,dc=tw
ENV LDAP_ROOTPWD password
ENV REDIS_HOST 163.21.249.80
ENV REDIS_PORT 6379
ENV REDIS_PASSWORD null
ENV CACHE_DRIVER redis
ENV SESSION_DRIVER redis

COPY htdocs /var/www/localhost/htdocs
RUN composer update \
    && chown -R apache:apache /var/www 
