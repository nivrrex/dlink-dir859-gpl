#!bin/sh
echo [$0] ... > /dev/console
sw1=`xmldbc -g /runtime/device/sw1`
sw2=`xmldbc -g /runtime/device/sw2`
xmldbc -X /runtime/device/SIM
xmldbc -X /runtime/auto_config
if [ -f "/var/run/chat_xmlnode.conf" ];then
	rm /var/run/chat_xmlnode.conf
fi
if [ "$sw1" = "0" -a "$sw2" = "1" ]; then
	service WAN stop
elif [ "$sw1" = "1" -a "$sw2" = "0" ]; then
	service INET.WAN-3 stop
fi
exit 0
