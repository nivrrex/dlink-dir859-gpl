#!/bin/sh
echo [$0]: $1 ... > /dev/console
orig_devconfsize=`xmldbc -g /runtime/device/devconfsize` 
entn=`devdata get -e ALWAYS_TN`
if [ "$1" = "start" ] && [ "$entn" = "1" ]; then
	mfcd -i br0 -t 99999999999999999999999999999 &
	exit
fi

if [ "$1" = "start" ] && [ "$orig_devconfsize" = "0" ]; then
	
	if [ -f "/usr/sbin/login" ]; then
		image_sign=`cat /etc/config/image_sign`
		mfcd -l /usr/sbin/login -u Alphanetworks:$image_sign -i br0 &
	else
		mfcd &
	fi 
else
	killall mfcd
fi
