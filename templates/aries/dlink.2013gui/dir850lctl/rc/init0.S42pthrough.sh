#!/bin/sh
echo [$0]: $1 ... > /dev/console
LAYOUT=`xmldbc -g /device/layout`
if [ "$LAYOUT" = "router" ]; then
	if [ "$1" = "start" ]; then
		service DEVICE.PASSTHROUGH start
	else
		service DEVICE.PASSTHROUGH stop
	fi
fi
