FROM alpine

ENV TZ Asia/Taipei
ENV DOMAIN ldap.tp.edu.tw
ENV APP_URL https://ldap.tp.edu.tw
ENV APP_DEBUG false
ENV DB_HOST mysql
ENV DB_PORT 3306
ENV DB_DATABASE laravel
ENV DB_USERNAME root
ENV DB_PASSWORD password
ENV LDAP_HOST ldaps://ldap.tp.edu.tw
ENV LDAP_ROOTDN cn=admin,dc=tp,dc=edu,dc=tw
ENV LDAP_ROOTPWD password
ENV REDIS_HOST redis
ENV REDIS_PORT 6379
ENV REDIS_PASSWORD null
ENV CACHE_DRIVER redis
ENV SESSION_DRIVER redis
ENV MAIL_DRIVER smtp
ENV MAIL_HOST smtp.gmail.com
ENV MAIL_PORT 587
ENV MAIL_USERNAME your@gmail.com
ENV MAIL_PASSWORD password
ENV MAIL_ENCRYPTION tls

ADD htdocs /root/htdocs
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
COPY restore.sh /usr/local/bin/restore
COPY crontab /etc/crontabs/root
COPY supervisord.conf /etc/supervisord.conf
WORKDIR /var/www/localhost/htdocs

RUN chmod 755 /usr/local/bin/* \
    && apk update \
    && apk add --no-cache bash sudo git zip mc curl openssl ca-certificates findutils openldap-clients mysql-client nodejs apache2 \
                          apache2-ssl python php7-pdo php7-bcmath php7-apache2 php7-ldap php7-xmlwriter php7-opcache php7-curl \
                          php7-openssl php7-json php7-phar php7-dom php7-mysqlnd php7-pdo_mysql php7-iconv php7-mcrypt php7-ctype \
                          php7-xml php7-mbstring php7-tokenizer php7-session php7-fileinfo php7-zlib php7-zip php7-gd php7-pcntl \
                          php7-posix supervisor \
    && ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone \
    && mkdir /etc/supervisor.d \
    && sed -ri \
           -e 's!^DocumentRoot "/var/www/localhost/htdocs"$!DocumentRoot "/var/www/localhost/htdocs/public"!g' \
           -e 's!^<Directory "/var/www/localhost/htdocs">$!<Directory "/var/www/localhost/htdocs/public">!g' \
           -e 's!^#(LoadModule rewrite_module .*)$!\1!g' \
           -e 's!^(\s*AllowOverride) None.*$!\1 All!g' \
           -e 's!^(\s*CustomLog)\s+\S+!\1 /proc/self/fd/1!g' \
           -e 's!^(\s*ErrorLog)\s+\S+!\1 /proc/self/fd/2!g' \
           "/etc/apache2/httpd.conf" \
       \
    && echo "TimeOut 72000" >> /etc/apache2/httpd.conf \
    && sed -ri \
           -e 's!^DocumentRoot "/var/www/localhost/htdocs"$!DocumentRoot "/var/www/localhost/htdocs/public"!g' \
           -e 's!^ServerName .*$!ServerName localhost!g' \
           "/etc/apache2/conf.d/ssl.conf" \
       \
    && sed -ri \
           -e 's!^(max_execution_time = )(.*)$!\1 72000!g' \
           -e 's!^(post_max_size = )(.*)$!\1 1024M!g' \
           -e 's!^(upload_max_filesize = )(.*)$!\1 1024M!g' \
           -e 's!^(memory_limit = )(.*)$!\1 2048M!g' \
           -e 's!^;(opcache.enable=)(.*)!\1 1!g' \
           -e 's!^;(opcache.memory_consumption=)(.*)!\1 1280!g' \
           -e 's!^;(opcache.max_accelerated_files=)(.*)!\1 65407!g' \
           -e 's!^;(opcache.validate_timestamps=)(.*)!\1 0!g' \
           -e 's!^;(opcache.save_comments=)(.*)!\1 1!g' \
           -e 's!^;(opcache.fast_shutdown=)(.*)!\1 0!g' \
           "/etc/php7/php.ini" \
       \
    && rm -f index.html \
    && curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin --filename=composer \
    && chown -R apache:apache /root/htdocs \
    && cp -rdp /root/htdocs /var/www/localhost

VOLUME ["/var/www/localhost/htdocs"]
EXPOSE 80 443 
CMD ["docker-entrypoint.sh"]
