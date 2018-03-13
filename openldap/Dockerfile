FROM osixia/openldap-backup

ADD bootstrap /container/service/slapd/assets/config/bootstrap
ADD environment /container/environment/01-custom

VOLUME ["/container/service/slapd/assets/certs","/etc/ldap/slapd.d","/var/lib/ldap","/data/backup"]
