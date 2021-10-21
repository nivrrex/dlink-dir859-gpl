#!/bin/sh
echo [$0] $1 $2 $3 $4 $5 $6 $7 $8 ... > /dev/console
xmldbc -P /etc/scripts/usb3gkit.php -V ACTION=$1 -V SLOT=$2 -V DEVPATH=$3 -V DEVNUM=$4 -V INFNUM=$5 -V VID=$6 -V PID=$7 -V DEVNAME=$8 > /var/usb3gkit.$2.sh
sh /var/usb3gkit.$2.sh
