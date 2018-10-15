#!/bin/sh
set -e

# prepare vpn
mkdir -p /dev/net
if [ ! -c /dev/net/tun ]; then
	mknod /dev/net/tun c 10 200
  chmod 0666 /dev/net/tun
fi

openvpn --script-security 2 --up /etc/openvpn/up.sh \
	--status /etc/openvpn/client.status 10 --redirect-gateway local \
	--cd /etc/openvpn --config /etc/openvpn/client.conf

sleep 5
ip route del default
ip route add default dev eth0

radiusd -xx -f
