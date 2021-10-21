#!/bin/sh
echo [$0]: $1 ... > /dev/console
if [ "$1" = "start" ]; then
	event "BRIDGE-1.DOWN"		insert "usockc /var/gpio_ctrl STATUS_AMBER_BLINK"
	event "BRIDGE-1.UP"		insert "usockc /var/gpio_ctrl STATUS_GREEN"
	event "STATUS.CRITICAL"	add "usockc /var/gpio_ctrl STATUS_AMBER"

#	event "STATUS.READY"		add "usockc /var/gpio_ctrl STATUS_GREEN"
#	event "STATUS.NOTREADY"		add "usockc /var/gpio_ctrl STATUS_AMBER"
	
	event "STATUS.GREEN"		add "usockc /var/gpio_ctrl STATUS_GREEN"
	event "STATUS.GREEBBLINK"	add "usockc /var/gpio_ctrl STATUS_GREEN_BLINK"
	
	event "STATUS.AMBER"		add "usockc /var/gpio_ctrl STATUS_AMBER"
	event "STATUS.AMBERBLINK"	add "usockc /var/gpio_ctrl STATUS_AMBER_BLINK"

	event "WAN-1.CONNECTED"		insert "WANLED:phpsh /etc/scripts/update_wanled.php EVENT=WAN_CONNECTED"
	event "WAN-1.PPP.ONDEMAND"	insert "WANLED:phpsh /etc/scripts/update_wanled.php EVENT=WAN_PPP_ONDEMAND"
	event "WAN-1.DISCONNECTED"	insert "WANLED:phpsh /etc/scripts/update_wanled.php EVENT=WAN_DISCONNECTED"
	event "WANPORT.LINKUP"	insert "WANLED:phpsh /etc/scripts/update_wanled.php EVENT=WAN_LINKUP"
	event "WANPORT.LINKDOWN"	insert "WANLED:phpsh /etc/scripts/update_wanled.php EVENT=WAN_LINKDOWN"
	event "WPS.INPROGRESS"		add "usockc /var/gpio_ctrl WPS_IN_PROGRESS"
	event "WPS.SUCCESS"			add "usockc /var/gpio_ctrl WPS_SUCCESS"
	event "WPS.OVERLAP"			add "usockc /var/gpio_ctrl WPS_OVERLAP"
	event "WPS.ERROR"			add "usockc /var/gpio_ctrl WPS_ERROR"
	event "WPS.NONE"			add "usockc /var/gpio_ctrl WPS_NONE"
	event "WLAN.CONNECTED"			add "usockc /var/gpio_ctrl WIFI_GREEN_BLINK"	
	event "WLAN.DISCONNECTED"			add "usockc /var/gpio_ctrl WIFI_OFF"
	
	event "INET_UNLIGHT"	add "usockc /var/gpio_ctrl INET_UNLIGHT"
	event "INET_RECOVER"	add "usockc /var/gpio_ctrl INET_RECOVER"

fi
