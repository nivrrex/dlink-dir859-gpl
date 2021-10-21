#!/bin/sh
echo [$0]: $1 ... > /dev/console
LAYOUT=`xmldbc -g /device/layout`
if [ "$1" = "start" ]; then
	event INFSVCS.LAN-1.UP		add "event STATUS.READY"
	event INFSVCS.BRIDGE-1.UP	add "event STATUS.READY"
	event INFSVCS.LAN-1.DOWN	add "event STATUS.NOTREADY"
	event INFSVCS.BRIDGE-1.DOWN	add "event STATUS.NOTREADY"

	service BRIDGE start
	if [ "$LAYOUT" = "router" ]; then
		service LAN start
		service WAN start
		service HW_NAT start
	fi
else
	if [ "$LAYOUT" = "router" ]; then
		service HW_NAT stop
		service WAN stop
		service LAN stop
	fi
	service BRIDGE stop
fi
