#!/bin/sh
echo [$0]: $1 ... > /dev/console
LAYOUT=`xmldbc -g /device/layout`
if [ "$LAYOUT" = "router" ]; then
	if [ "$1" = "start" ]; then
		(sleep 2; service SHAREPORT start)&
	else
		service SHAREPORT stop
fi
fi
