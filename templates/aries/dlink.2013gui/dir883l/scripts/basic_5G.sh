#!/bin/sh

if [ $2 = "Basic" ]; then
	if [ $# -ne 2 ]; then
		echo "Usage:"
		echo "/etc/scripts/basic_5G.sh <SSID> <Authentication protocols> "
		echo "Authentication protocols: Basic | WPA | 11i | WPAand11i"
		echo "example: /etc/scripts/basic_5G.sh DIR883L5G Basic"
		exit 1
	fi
else
	if [ $# -ne 4 ]; then
		echo "Usage:"
		echo "/etc/scripts/encrypt_5G.sh <SSID> <Authentication protocols> <Encryption type> <Password>"
		echo "Authentication protocols: Basic | WPA | 11i | WPAand11i"
		echo "Encryption type: TKIPEncryption | AESEncryption | TKIPandAESEncryption"
		echo "example: /etc/scripts/encrypt_5G.sh DIR883L5G WPAand11i TKIPandAESEncryption 12345678"
		exit 1
	fi
fi

LSMOD=`lsmod | grep -r qdpc_host`

if [ "$LSMOD" = "" ]; then
	echo "Wait about 15 secs for QTA module booting............."
	ifconfig br0:1 1.1.1.1
	insmod lib/modules/qdpc-host.ko 
	sleep 1
	ifconfig host0 up 
	brctl addif br0 host0
	sleep 15
fi

ping 1.1.1.2 | grep alive
while [ $? = "" ]
do
	sleep 1
	ping 1.1.1.2 | grep alive
done

echo "Start set basic 5G setting.............."
qcsapi_pcie rfenable wifi0 1
sleep 1
qcsapi_pcie set_SSID wifi0 "$1"
sleep 1
qcsapi_pcie set_beacon wifi0 $2
if [ $2 != "Basic" ]; then
	sleep 1
	qcsapi_pcie set_WPA_encryption_modes wifi0 $3
	sleep 1
	qcsapi_pcie set_passphrase wifi0 0 $4
	sleep 1
fi
