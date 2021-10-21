#!/bin/sh
case "$1" in
NEW_CLIENT)
echo [$0] $1 $2 $3 ....
#	logger -p notice -t WIFI "WAN associated with [$3] from wireless client"
	;;
ACTION)
	phpsh /etc/scripts/wisp.php ACTION=$2
	;;
*)
#	echo "not support [$1] ..."
	;;
esac
exit 0
