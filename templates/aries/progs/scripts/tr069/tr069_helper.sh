#!/bin/sh
CONSOLE=/dev/console
execfile=/etc/scripts/tr069/tr069_helper.php
multiobj_file=/etc/scripts/tr069/multiobject/$1.php
exec_shell_file=/var/run/tr69c_$$.sh
exec_helper=/etc/scripts/tr069/tr069_exec.sh
exec_basic="xmldbc -P $execfile -V BASIC_OP_FLAG=1"
exec_singleobj_datamodel="xmldbc -P $execfile -V EXEC_SHELL_FILE=$exec_shell_file"
exec_multiobj_datamodel="xmldbc -P $multiobj_file -V EXEC_SHELL_FILE=$exec_shell_file"
#echo "[$0]: $#($@)" > $CONSOLE

case "$1" in
#For Basic Operations Begin
	GET)
		case "$2" in
			phyinf | wan_mode)
				$exec_basic -V ACTION="$1" -V NAME="$2" -V UID="$3"
				;;
			*)
				$exec_basic -V ACTION="$1" -V NAME="$2"
				;;
		esac
		;;
	SET)
		$exec_basic -V ACTION="$1" -V NAME="$2" -V SET_VALUE="$3"
		;;
	DEL)
		$exec_basic -V ACTION="$1" -V NAME="$2"
		;;
	GET_ATTR)
		$exec_basic -V ACTION="$1" -V NAME="$2" -V ATTR_NAME="$3"
		;;
	SET_ATTR)
		$exec_basic -V ACTION="$1" -V NAME="$2" -V ATTR_NAME="$3" -V ATTR_VALUE="$4"
		;;
#For Basic Operations End
#For Multi-Object Begin
	#Level 1
	#InternetGatewayDevice.Layer3Forwarding.Forwarding.{i}.
	#InternetGatewayDevice.WANDevice.{i}.
	#InternetGatewayDevice.LANDevice.{i}.
	#InternetGatewayDevice.ManagementServer.ManageableDevice.{i}.
	Forwarding | WANDevice | LANDevice | ManageableDevice) 
		case "$2" in
			GET_PATH | GET)
				$exec_multiobj_datamodel -V NAME="$1" -V ACTION="$2" -V PARAM_NAME="$3" -V INDEX0="$4"
				;;
			GET_INDEX)
				$exec_multiobj_datamodel -V NAME="$1" -V ACTION="$2" -V INSTANCE="$3"
				;;
			SET)
				$exec_multiobj_datamodel -V NAME="$1" -V ACTION="$2" -V PARAM_NAME="$3" -V INDEX0="$4" -V SET_VALUE="$5"
				if [ -f "$exec_shell_file" ]; then
					sh $exec_helper "$1.$4" "$exec_shell_file"
				fi
				;;
			DEL)
				$exec_multiobj_datamodel -V NAME="$1" -V ACTION="$2" -V INDEX0="$3"
				if [ -f "$exec_shell_file" ]; then
					sh $exec_helper "$1.$4" "$exec_shell_file"
				fi
				;;
		esac
		;;
	#Level 2
	#InternetGatewayDevice.WANDevice.{i}.WANConnectionDevice.{i}.
	#InternetGatewayDevice.LANDevice.{i}.LANHostConfigManagement.IPInterface.{i}.
	#InternetGatewayDevice.LANDevice.{i}.LANEthernetInterfaceConfig.{i}.
	#InternetGatewayDevice.LANDevice.{i}.Hosts.Host.{i}.
	#InternetGatewayDevice.LANDevice.{i}.WLANConfiguration.{i}.
	WANConnectionDevice | IPInterface | LANEthernetInterfaceConfig | Host | WLANConfiguration)
		case "$2" in
			GET_PATH | GET)
				$exec_multiobj_datamodel -V NAME="$1" -V ACTION="$2" -V PARAM_NAME="$3" -V INDEX0="$4" -V INDEX1="$5"
				;;
			GET_INDEX)
				$exec_multiobj_datamodel -V NAME="$1" -V ACTION="$2" -V INDEX0="$3" -V INSTANCE="$4"
				;;
			SET)
				$exec_multiobj_datamodel -V NAME="$1" -V ACTION="$2" -V PARAM_NAME="$3" -V INDEX0="$4" -V INDEX1="$5" -V SET_VALUE="$6"
				if [ -f "$exec_shell_file" ]; then
					sh $exec_helper "$1.$4.$5" "$exec_shell_file"
				fi
				;;
			DEL)
				$exec_multiobj_datamodel -V NAME="$1" -V ACTION="$2" -V INDEX0="$3" -V INDEX1="$4"
				if [ -f "$exec_shell_file" ]; then
					sh $exec_helper "$1.$4.$5" "$exec_shell_file"
				fi
				;;
		esac
		;;
	#Level 3
	#InternetGatewayDevice.WANDevice.{i}.WANConnectionDevice.{i}.WANIPConnection.{i}.
	#InternetGatewayDevice.WANDevice.{i}.WANConnectionDevice.{i}.WANPPPConnection.{i}.
	WANIPConnection | WANPPPConnection) 
		case "$2" in
			GET_PATH | GET)
				$exec_multiobj_datamodel -V NAME="$1" -V ACTION="$2" -V PARAM_NAME="$3" -V INDEX0="$4" -V INDEX1="$5" -V INDEX2="$6"
				;;
			GET_INDEX)
				$exec_multiobj_datamodel -V NAME="$1" -V ACTION="$2" -V INDEX0="$3" -V INDEX1="$4" -V INSTANCE="$5"
				;;
			SET)
				$exec_multiobj_datamodel -V NAME="$1" -V ACTION="$2" -V PARAM_NAME="$3" -V INDEX0="$4" -V INDEX1="$5" -V INDEX2="$6" -V SET_VALUE="$7"
				if [ -f "$exec_shell_file" ]; then
					sh $exec_helper "$1.$4.$5.$6" "$exec_shell_file"
				fi
				;;
			DEL)
				$exec_multiobj_datamodel -V NAME="$1" -V ACTION="$2" -V INDEX0="$3" -V INDEX1="$4" -V INDEX2="$5"
				if [ -f "$exec_shell_file" ]; then
					sh $exec_helper "$1.$4.$5" "$exec_shell_file"
				fi
				;;
		esac
		;;
	#Level 4
	#InternetGatewayDevice.WANDevice.{i}.WANConnectionDevice.{i}.WANPPPConnection.{i}.PortMapping.{i}.
	PortMapping)
		case "$2" in
			GET_PATH | GET)
				$exec_multiobj_datamodel -V NAME="$1" -V ACTION="$2" -V PARAM_NAME="$3" -V INDEX0="$4" -V INDEX1="$5" -V INDEX2="$6" -V INDEX3="$7"
				;;
			GET_INDEX)
				$exec_multiobj_datamodel -V NAME="$1" -V ACTION="$2" -V INDEX0="$3" -V INDEX1="$4" -V INDEX2="$5" -V INSTANCE="$6"
				;;
			SET)
				$exec_multiobj_datamodel -V NAME="$1" -V ACTION="$2" -V PARAM_NAME="$3" -V INDEX0="$4" -V INDEX1="$5" -V INDEX2="$6" -V INDEX3="$7" -V SET_VALUE="$8"
				if [ -f "$exec_shell_file" ]; then
					sh $exec_helper "$1.$4.$5.$6.$7" "$exec_shell_file"
				fi
				;;
			DEL)
				$exec_multiobj_datamodel -V NAME="$1" -V ACTION="$2" -V INDEX0="$3" -V INDEX1="$4" -V INDEX2="$5" -V INDEX3="$6" 
				if [ -f "$exec_shell_file" ]; then
					sh $exec_helper "$1.$4.$5.$6.$7" "$exec_shell_file"
				fi
				;;
		esac	
		;;
#For Multi-Object End
#For Single-Object Begin
	*)
		$exec_singleobj_datamodel -V NAME="$1" -V ACTION="$2" -V SET_VALUE="$3"
		if [ -f "$exec_shell_file" ]; then
			sh $exec_helper "$1" "$exec_shell_file" 
		fi
		;;
#For Single-Object End
esac

exit 0