#!/bin/sh
echo [$0]: $1 ... > /dev/console
if [ "$1" = "start" ]; then
    event "TTY.ATTACH"		add "/etc/events/ttyplugin.sh"
	event "TTY.DETTACH"		add "/etc/events/ttyplugoff.sh"
	event "DIALINIT"        add "/etc/events/DIALINIT.3G.sh"	
fi
