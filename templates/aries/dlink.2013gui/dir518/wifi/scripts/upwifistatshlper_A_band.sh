#!/bin/sh
case "$1" in
NEW_CLIENT)
	echo [$0] $1 $2 $3 ....
	if [ $2 -eq 1 ]; then
		logger -p notice -t WIFI "Got new client [$3] associated from BAND5G-1.2 (5 Ghz)"
	else
	logger -p notice -t WIFI "Got new client [$3] associated from BAND5G-1.1 (5 Ghz)"
	fi
	;;
*)
#	echo "not support [$1] ..."
	;;
esac
exit 0
