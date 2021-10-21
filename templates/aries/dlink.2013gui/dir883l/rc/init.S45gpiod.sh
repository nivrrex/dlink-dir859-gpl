#!/bin/sh

# Leotest gpio signal with debugfs

insmod ./lib/modules/gpio_keys.ko
insmod ./lib/modules/button-hotplug.ko
insmod ./lib/modules/leds-gpio.ko

wanidx=`xmldbc -g /device/router/wanindex`
if [ "$wanidx" != "" ]; then
	        gpiod -w $wanidx &
	else
		gpiod &
	fi

