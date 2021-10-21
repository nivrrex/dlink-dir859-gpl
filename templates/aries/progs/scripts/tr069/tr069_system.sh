#!/bin/sh
CONSOLE=/dev/console
#echo "$0: $#($@)" > $CONSOLE

case "$1" in
	REBOOT | FRESET | DBSAVE)
		event "$1"
		;;
	IMAGE)
		case "$2" in
			VALIDATE)
				case "$3" in
					FIRMWARE)
						fwupdater -v "$4" -t "FIRMWARE"
					;;
					CONFIG)
						fwupdater -v "$4" -t "CONFIG"
					;;
				esac
				if [ "$?" = "0" ]; then
					echo "0"
				else
					echo "-1"
				fi
			;;
			UPGRADE)
				case "$3" in
					FIRMWARE)
						sh /etc/events/FWUPDATER.sh
					;;
					CONFIG)
						sh /etc/events/CFGUPDATER.sh "$4" "0"
					;;
				esac
			;;
			DUMP)
				case "$3" in
					CONFIG)
						xmldbc -d /var/config.xml
						gzip /var/config.xml
						mv -f /var/config.xml.gz "$4"
					;;
					LOG)
						xmldbc -w /runtime/log > "$4"
					;;
				esac
			;;
		esac
		;;
	*)
		echo "$0 ($#($@)): Unknown action type!" > $CONSOLE
		;;
esac