FROM alpine

ADD docker-entrypoint.sh /usr/local/bin/
ADD setgw.sh /etc/openvpn/setgw.sh

RUN apk add --no-cache bash openvpn freeradius freeradius-ldap freeradius-perl freeradius-radclient \
    && sed -ri "s!destination = files!destination = stderr!g" /etc/raddb/radiusd.conf \
    && chmod 0711 /usr/local/bin/docker-entrypoint.sh \
    && chmod 0711 /etc/openvpn/setgw.sh

ADD client.conf /etc/openvpn/client.conf
ADD default /etc/raddb/sites-enabled/default
ADD inner-tunnel /etc/raddb/sites-enabled/inner-tunnel
ADD clients.conf /etc/raddb/clients.conf
ADD proxy.conf /etc/raddb/proxy.conf
ADD ldap /etc/raddb/mods-enabled/ldap

VOLUME ["/etc/raddb/", "/etc/openvpn/"]
EXPOSE 1812/udp 1813/udp 1194/udp 18120
CMD ["docker-entrypoint.sh"]
