#!/bin/sh
echo [$0]: $1 ... > /dev/console
if [ "$1" = "start" ]; then
event BRIDGE-1.UP	add "service INFSVCS.BRIDGE-1 start"
event BRIDGE-1.DOWN	add "service INFSVCS.BRIDGE-1 stop"
event BRIDGE-2.UP	add "service INFSVCS.BRIDGE-2 start"
event BRIDGE-2.DOWN	add "service INFSVCS.BRIDGE-2 stop"
event BRIDGE-3.UP	add "service INFSVCS.BRIDGE-3 start"
event BRIDGE-3.DOWN	add "service INFSVCS.BRIDGE-3 stop"

event REBOOT		add "/etc/events/reboot.sh"
event FRESET		add "/etc/events/freset.sh"
event UPDATERESOLV	add "/etc/events/UPDATERESOLV.sh"
event SEALPAC.SAVE	add "/etc/events/SEALPAC-SAVE.sh"
event SEALPAC.LOAD	add "/etc/events/SEALPAC-LOAD.sh"
event SEALPAC.CLEAR	add "/etc/events/SEALPAC-CLEAR.sh"
event SITESURVEY	add "/etc/events/SITESURVEY.sh"
event SHOWMAC		add "/etc/events/SHOWMAC.sh"
event DBSAVE		add "/etc/scripts/dbsave.sh"
event DHCPS4-STOP.BRIDGE-1  add "/etc/events/DHCPS4-STOP.sh BRIDGE-1"

event SEALPAC.LOAD
service LOGD start &
service LOGD alias DEVICE.LOG
fi
