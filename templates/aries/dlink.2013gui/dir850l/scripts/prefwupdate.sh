#!/bin/sh
echo "[$0] ...." > /dev/console
service WAN stop
service WIFI stop
service LAN stop
event FWUPDATE
sleep 3
event HTTP.DOWN add /etc/events/FWUPDATER.sh
service HTTP stop
exit 0
