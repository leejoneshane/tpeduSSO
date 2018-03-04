FROM leejoneshane/laravel

ENV DOMAIN ldap.tp.edu.tw
ENV MAIL leejoneshane@gmail.com
ENV WEB_PASSWORD password
ENV TZ Asia/Taipei
ENV DB_HOST 163.21.249.81
ENV DB_PORT 3306
ENV DB_DATABASE laravel
ENV DB_USERNAME root
ENV DB_PASSWORD password
ENV LDAP_HOST ldaps://163.21.249.83
ENV LDAP_ROOTDN cn=admin,dc=tp,dc=edu,dc=tw
ENV LDAP_ROOTPWD password
ENV REDIS_HOST 163.21.249.81
ENV REDIS_PORT 6379
ENV REDIS_PASSWORD null
ENV CACHE_DRIVER redis
ENV SESSION_DRIVER redis

COPY htdocs /var/www/localhost/htdocs
RUN chown -R apache:apache /var/www 
