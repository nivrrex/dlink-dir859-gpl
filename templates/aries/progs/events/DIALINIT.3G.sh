#!/bin/sh
echo [$0] ... > /dev/console
vid=`xmldbc -g /runtime/tty/entry:1/vid`
pid=`xmldbc -g /runtime/tty/entry:1/pid`
devname=`xmldbc -g /runtime/tty/entry:1/cmdport/devname`
if [ "$vid" == "" ] || [ "$pid" == "" ] || [ "$devname" == "" ]; then
        exit 0;
fi
if [ "$vid" == "19d2" ] && [ "$pid" == "0094" ];then
	chat -v -D $devname OK-AT-OK 
	sleep 1
fi
if [ "$vid" == "106c" ] && [ "$pid" == "3716" ];then
	chat -v -D $devname OK-AT-OK 
	sleep 1
fi
if [ "$vid" == "1e0e" ] && [ "$pid" == "ce16" ];then
	chat -v -D $devname OK-ATH-OK
	sleep 1
fi
if [ "$vid" == "1e0e" ] && [ "$pid" == "ce17" ];then
	chat -v -D $devname OK-ATH-OK
	sleep 1
fi
if [ "$vid" == "07d1" ] && [ "$pid" == "3e01" ];then
	apn=`xmldbc -g /runtime/tty/entry:1/apn`
	chat -v -D $devname OK-AT+CGDCONT=1,\"IP\",\"$apn\"-OK 
	sleep 1
fi
exit 0;
