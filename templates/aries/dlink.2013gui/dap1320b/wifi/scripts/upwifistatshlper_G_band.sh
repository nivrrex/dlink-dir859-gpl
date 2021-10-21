#!/bin/sh
echo [$0] $1 $2 $3 ....

apidx=`expr $2 + 1`

case "$1" in
NEW_CLIENT)
	logger -p notice -t WIFI "Got new client [$3] associated from BAND24G-1.$apidx (2.4 Ghz)"
	;;
*)
	echo "not support [$1] ..."
	;;
esac
exit 0
