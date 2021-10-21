#!/bin/sh
echo [$0] ["$1"] [$2] [$3] [$4] [$5]> /dev/console
phpsh /etc/scripts/wps/wps.php PARAM1=save PARAM2=REPEATER BAND=5G SSID="$1" AUTHMODE=$2 ENCRTYPE=$3 KEYINDEX=$4 KEYSTR="$5"
exit 0 
