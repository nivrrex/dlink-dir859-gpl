#!/bin/sh
echo [$0] ... > /dev/console
xmldbc -s /runtime/device/SIM/PINsts "CHECKING"
service INET.WAN-3 stop

sw1=`xmldbc -g /runtime/device/sw1`
sw2=`xmldbc -g /runtime/device/sw2`
if [ "$sw1" = "0" -a "$sw2" = "1" ]; then
#	service WAN stop
	mode=3G
elif [ "$sw1" = "1" -a "$sw2" = "0" ]; then
#	service INET.WAN-3 stop
	mode=normal
fi
devname=`xmldbc -g /runtime/tty/entry:1/cmdport/devname`
if [ "$devname" == "" ];then
	devname=`xmldbc -g /runtime/tty/entry:1/devname`
fi
if [ "$devname" != "" ]; then
	chat -e -D $devname OK-AT-OK 
	vid=`xmldbc -g /runtime/tty/entry:1/vid`
	pid=`xmldbc -g /runtime/tty/entry:1/pid`
	if [ "$vid" = "0x12d1"  -a "$pid" = "0x1001" ];then
		sleep 2
		chat -e -D $devname OK-ATE-OK
	fi
	sh /etc/events/dongle_check.sh
	for i in 1 2 3 4 5 6;do
		if [ ! -f "/var/run/chat_xmlnode.conf" ];then
			sleep 2
			imsi=`chat -e -v -c -D $devname OK-AT+CIMI-OK | sed '5,$d' | sed '1,3d'` 
		fi
	done
	sleep 2
	pinsts=`chat -e -D $devname OK-AT+CPIN?-OK | grep "+CPIN:"`
else
	xmldbc -s /runtime/device/SIM/PINsts ""
	exit 0
fi
PINsts=`echo $pinsts | cut -d: -f2 | cut -d: -f1 `
#next is a dispose shell to dispose zte 581/zte 560.
#PINsts=`echo $pinsts | cut -d: -f2 | cut -d: -f1 | cut -d';' -f1`
mcc=`cat /var/run/chat_xmlnode.conf | grep mcc=   | scut -f 2`
mnc2=`cat /var/run/chat_xmlnode.conf | grep mnc_1= | scut -f 2`
mnc3=`cat /var/run/chat_xmlnode.conf | grep mnc_2= | scut -f 2`

#" SIM PIN", " READY", " SIM failure", " SIM not inserted","NOSUPPORT"
#xmldbc -s /runtime/device/SIM/PINsts "$PINsts"
xmldbc -P /etc/events/sim_chk.php -V PINSTS="$PINsts" -V MCC="$mcc" -V MNC2="$mnc2" -V MNC3="$mnc3" -V MODE="$mode" > /var/run/chk_sim.sh
sh /var/run/chk_sim.sh
#sh /etc/scripts/dbsave.sh
exit 0
