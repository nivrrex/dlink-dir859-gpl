#!/bin/sh

status="`xmldbc -g /runtime/wifi_tmpnode/state`"

#if [ "$status" == "" -o "$status" == "ALLDONE" ]; then
xmldbc -s /runtime/wifi_tmpnode/state "DOING"
xmldbc -X /runtime/wifi_tmpnode/sitesurvey_2G
xmldbc -X /runtime/wifi_tmpnode/sitesurvey_5G
iwlist wlan0 scanning > /var/ssvy_5g.txt
Parse2DB sitesurvey -f /var/ssvy_5g.txt -i 5G -d > /dev/null
rm /var/ssvy_5g.txt
iwlist wlan1 scanning > /var/ssvy_24g.txt
Parse2DB sitesurvey -f /var/ssvy_24g.txt -i 24G -d > /dev/null
rm /var/ssvy_24g.txt
xmldbc -s /runtime/wifi_tmpnode/state "ALLDONE"
#fi

exit 0