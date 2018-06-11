FROM alpine

ENV LDAP_HOST ldap.tp.edu.tw
ENV LDAP_ROOTDN cn=admin,dc=tp,dc=edu,dc=tw
ENV LDAP_ROOTPWD test
ENV LDAP_BASEDN ou=account,dc=tp,dc=edu,dc=tw
ENV SECRET tpeduaaa
ENV PROXY_TO_HOST 163.21.249.130
ENV PROXY_SECRET tpeduaaa

RUN apk add --no-cache bash freeradius freeradius-ldap freeradius-radclient \
    && sed -ri "s!destination = files!destination = stderr!g" /etc/raddb/radiusd.conf

ADD default /etc/raddb/sites-enabled/default
ADD inner-tunnel /etc/raddb/sites-enabled/inner-tunnel
ADD clients.conf /etc/raddb/clients.conf
ADD ldap /etc/raddb/mods-enabled/ldap

VOLUME ["/etc/raddb/"]
EXPOSE 1812/udp 1813/udp 18120
CMD ["radiusd","-xx","-f"]
