FROM alpine
ENV POSTFIX_HOSTNAME: "example.com"
ENV POSTFIX_RELAY_HOST: "example.com:587"
ENV POSTFIX_RELAY_USER: "user"
ENV POSTFIX_RELAY_PASSWORD: "yourpassword"

ADD docker-entrypoint.sh /usr/local/bin/
RUN apk add --no-cache --update postfix cyrus-sasl cyrus-sasl-plain ca-certificates bash musl musl-utils \
    && chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 587
VOLUME ["/etc/ldap/slapd.d","/var/lib/ldap","/data/backup"]
ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["postfix", "start-fg"]
