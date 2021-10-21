#!/bin/sh

WIFIREG=`devdata get -e wifipower`
if [ "$WIFIREG" == "" ]; then
	WIFIREG=FCC
fi
cp /etc/SingleSKU24G/SingleSKU_$WIFIREG.dat /var/SingleSKU24G.dat

#we only insert wifi modules in init
insmod /lib/modules/rt2860v2_ap.ko

#dump channel list from driver
phpsh /etc/scripts/wlan_get_chanlist.php