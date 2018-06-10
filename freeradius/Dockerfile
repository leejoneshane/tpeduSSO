FROM alpine

ENV LDAP_HOST ldap.tp.edu.tw
ENV LDAP_ROOTDN cn=readonly,dc=tp,dc=edu,dc=tw
ENV LDAP_ROOTPWD test
ENV LDAP_BASEDN ou=people,dc=tp,dc=edu,dc=tw

RUN apk add --no-cache bash freeradius freeradius-ldap freeradius-radclient

ADD default /etc/raddb/sites-enabled/default
ADD inner-tunnel /etc/raddb/sites-enabled/inner-tunnel
ADD clients.conf /etc/raddb/clients.conf
ADD ldap /etc/raddb/mods-enabled/ldap

VOLUME ["/etc/raddb/"]
EXPOSE 1812/udp 1813/udp 18120
CMD ["radiusd","-xx","-f"]
