#!/bin/sh
echo [$0]: $1 ... > /dev/console
if [ "$1" = "start" ]; then
	event "STATUS.READY"		add "usockc /var/gpio_ctrl STATUS_GREEN"
    event "STATUS.CRITICAL"     add "usockc /var/gpio_ctrl STATUS_AMBER_BLINK"
	event "STATUS.NOTREADY"		add "usockc /var/gpio_ctrl STATUS_AMBER"
	event "STATUS.GREEN"		add "usockc /var/gpio_ctrl STATUS_GREEN"
	event "STATUS.GREEBBLINK"	add "usockc /var/gpio_ctrl STATUS_GREEN_BLINK"
	event "STATUS.AMBER"		add "usockc /var/gpio_ctrl STATUS_AMBER"
	event "FWUPDATE"			add "usockc /var/gpio_ctrl INET_AMBER_BLINK"
	event "INET.CONNECTED"		add "usockc /var/gpio_ctrl INET_GREEN"
	event "INET.DISCONNECTED"	add "usockc /var/gpio_ctrl INET_AMBER"
	event "WAN-1.CONNECTED"		insert "WANLED:phpsh /etc/scripts/update_wanled.php EVENT=WAN_CONNECTED"
	event "WAN-1.PPP.ONDEMAND"	insert "WANLED:phpsh /etc/scripts/update_wanled.php EVENT=WAN_PPP_ONDEMAND"
	event "WAN-1.PPP.MANUAL"      insert "WANLED:phpsh /etc/scripts/update_wanled.php EVENT=WAN_PPP_MANUAL"
	event "WAN-1.PPP.SCHEDULE"      insert "WANLED:phpsh /etc/scripts/update_wanled.php EVENT=WAN_PPP_SCHEDULE"
	event "WAN-2.CONNECTED"		add "null"
	event "WAN-1.DISCONNECTED"	insert "WANLED:phpsh /etc/scripts/update_wanled.php EVENT=WAN_DISCONNECTED"
	event "WAN-2.DISCONNECTED"	add "null"
	event "WANPORT.LINKUP"	insert "WANLED:phpsh /etc/scripts/update_wanled.php EVENT=WAN_LINKUP"
	event "WANPORT.LINKDOWN"	insert "WANLED:phpsh /etc/scripts/update_wanled.php EVENT=WAN_LINKDOWN"
	
	event "WPS.INPROGRESS"		add "usockc /var/gpio_ctrl WPS_IN_PROGRESS"
	event "WPS.SUCCESS"			add "usockc /var/gpio_ctrl WPS_SUCCESS"
	event "WPS.OVERLAP"			add "usockc /var/gpio_ctrl WPS_OVERLAP"
	event "WPS.ERROR"			add "usockc /var/gpio_ctrl WPS_ERROR"
	event "WPS.NONE"			add "usockc /var/gpio_ctrl WPS_NONE"
	event "WLAN.CONNECTED"      add "usockc /var/gpio_ctrl WLAN_ENABLED"
	event "WLAN.DISCONNECTED"   add "usockc /var/gpio_ctrl WLAN_DISABLED"
fi
