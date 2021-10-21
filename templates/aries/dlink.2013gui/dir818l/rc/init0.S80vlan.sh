#!/bin/sh
echo [$0]: $1 ... > /dev/console
if [ "$1" = "start" ]; then
	service DEVICE.VLAN start
else
	service DEVICE.VLAN stop
fi
