#!/bin/sh


if [ "$1" == "stop" ]; then
	service DHCPS4.BRIDGE-1 stop
	exit 0
fi

if [ -f "/var/run/wifi_5Gfirst.pid" ]; then
	if [ ! -f "var/servd/BRIDGE-1-udhcpd.pid" ]; then
		echo "root AP no find"
		xmldbc -s /inf:1/dhcps4 DHCPS4-1
		service DHCPS4.BRIDGE-1 restart
	fi
	xmldbc -k REPEATER_DHCPS 
	xmldbc -t "REPEATER_DHCPS:10:sh /etc/scripts/REPEATER_DHCPS.sh"
	exit 0
fi

if [ -f "/var/run/wlan0-vxd.UP" ]; then
	ifname="wlan0-vxd"
elif [ -f "/var/run/wlan1-vxd.UP" ]; then
	ifname="wlan1-vxd"
else
	exit 0;
fi

current_state=`iwlist $ifname state`

if [ "$current_state" == "Connected" ] && [ -f "var/servd/BRIDGE-1-udhcpd.pid" ]; then
	echo $current_state
	xmldbc -s /inf:1/dhcps4 ""
	service DHCPS4.BRIDGE-1 stop
elif [ "$current_state" != "Connected" ] && [ ! -f "var/servd/BRIDGE-1-udhcpd.pid" ]; then
	echo $current_state
	xmldbc -s /inf:1/dhcps4 DHCPS4-1
	service DHCPS4.BRIDGE-1 restart
fi
xmldbc -k REPEATER_DHCPS
xmldbc -t "REPEATER_DHCPS:10:sh /etc/scripts/REPEATER_DHCPS.sh"
exit 0
