FROM osixia/openldap-backup

ADD bootstrap /container/service/slapd/assets/config/bootstrap
ADD environment /container/environment/01-custom
ADD slapd-restore /container/service/slapd-backup/assets/tool/slapd-restore

RUN chmod +x /container/service/slapd-backup/assets/tool/slapd-restore

EXPOSE 389

VOLUME ["/etc/ldap/slapd.d","/var/lib/ldap","/data/backup"]
