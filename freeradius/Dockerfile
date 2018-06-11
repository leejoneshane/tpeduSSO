FROM alpine

ENV LDAP_HOST ldap.tp.edu.tw
ENV LDAP_ROOTDN cn=admin,dc=tp,dc=edu,dc=tw
ENV LDAP_ROOTPWD test
ENV LDAP_BASEDN ou=account,dc=tp,dc=edu,dc=tw

RUN apk add --no-cache bash freeradius freeradius-ldap freeradius-radclient \
    && sed -ri "s!destination = files!destination = stderr!g" /etc/raddb/radiusd.conf

ADD default /etc/raddb/sites-enabled/default
ADD inner-tunnel /etc/raddb/sites-enabled/inner-tunnel
ADD clients.conf /etc/raddb/clients.conf
ADD ldap /etc/raddb/mods-enabled/ldap

VOLUME ["/etc/raddb/"]
EXPOSE 1812/udp 1813/udp 18120
CMD ["radiusd","-xx","-f"]
