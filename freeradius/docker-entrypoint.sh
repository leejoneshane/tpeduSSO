#!/bin/sh
set -e

echo prepare vpn
mkdir -p /dev/net
if [ ! -c /dev/net/tun ]; then
  mknod /dev/net/tun c 10 200
  chmod 0666 /dev/net/tun
fi

echo start openvpn client ....
openvpn --script-security 2 --up /etc/openvpn/up.sh \
	--status /etc/openvpn/client.status 10 --redirect-gateway def1 \
	--cd /etc/openvpn --config client.conf &

echo restore default gateway ....
sleep 5
ip route del 0.0.0.0/1
ip route del 128.0.0.0/1

echo start freeradius ....
radiusd -xx -f &
