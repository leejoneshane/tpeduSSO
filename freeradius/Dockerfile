FROM alpine

ENV LDAP_HOST ldaps://ldap.tp.edu.tw
ENV LDAP_ROOTDN cn=readonly,dc=tp,dc=edu,dc=tw
ENV LDAP_ROOTPWD test
ENV LDAP_BASEDN ou=people,dc=tp,dc=edu,dc=tw

ADD default /etc/raddb/sites-enabled/default
ADD clients.conf /etc/raddb/clients.conf
ADD ldap /etc/raddb/mods-enabled/ldap
ADD entrypoint.sh /usr/sbin/entrypoint.sh

RUN apk add --no-cache bash freeradius freeradius-ldap freeradius-eap freeradius-radclient openssl

VOLUME ["/etc/raddb/"]
EXPOSE 1812/udp 1813/udp 18120
ENTRYPOINT ["entrypoint.sh"]
