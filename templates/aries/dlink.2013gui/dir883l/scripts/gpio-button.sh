#!/bin/sh
# gpio-button: Send usockc to gpio daemon(gpiod.c).

case "$1" in
reset)
    if [ "$2" == "pressed" ]; then
        usockc /var/gpio_ctrl BUTTON_RESET_PRESSED
    fi
    if [ "$2" == "released" ]; then
        usockc /var/gpio_ctrl BUTTON_RESET_RELEASED
    fi
    ;;
wps)
    if [ "$2" == "pressed" ]; then
        usockc /var/gpio_ctrl BUTTON_WPS_PRESSED
    fi
    if [ "$2" == "released" ]; then
        usockc /var/gpio_ctrl BUTTON_WPS_RELEASED
    fi
	;;
esac

exit 0
