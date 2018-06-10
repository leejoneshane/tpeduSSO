#!/bin/sh
set -e

if [[ ! -f /etc/raddb/is.done ]]; then
    sed -ri \
        -e "s#LDAP_HOST#${LDAP_HOST}#g" \
        -e "s#LDAP_ROOTDN#${LDAP_ROOTDN}#g" \
        -e "s#LDAP_ROOTPWD#${LDAP_ROOTPWD}#g" \
        -e "s#LDAP_BASEDN#${LDAP_BASEDN}#g" \
        "/etc/raddb/mods-available/ldap"
    touch /etc/raddb/is.done
fi

exec /usr/sbin/radiusd -xx -f

if [ "$#" -lt 1 ]; then
  exec bash
else
  exec "$@"
fi
