#!/bun/sh

if [ "$1" = "STA" ]; then
	wlan24g=wlan1
	wlan5g=wlan0
elif [ "$1" = "REPEATER" ]; then
	wlan24g=wlan1-vxd
	wlan5g=wlan0-vxd
fi

if [ ! -f /var/run/$wlan5g.UP ]; then
	iwpriv $wlan5g set_mib func_off=1
	#ifconfig $wlan5g down
fi

if [ ! -f /var/run/$wlan24g.UP ]; then
	iwpriv $wlan24g set_mib func_off=1
	#ifconfig $wlan24g down
fi
exit 0
