FROM alpine

ADD docker-entrypoint.sh /usr/local/bin/
RUN apk add --no-cache --update postfix cyrus-sasl cyrus-sasl-plain ca-certificates bash musl musl-utils \
    && chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 587
VOLUME ["/etc/ldap/slapd.d","/var/lib/ldap","/data/backup"]
ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["postfix", "start-fg"]
