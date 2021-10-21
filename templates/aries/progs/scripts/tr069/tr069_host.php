<?
include "/etc/scripts/tr069/phplib/api.php";

function get_inf_uid($inf_name)
{
	$base = "/runtime/inf";
	foreach($base){
		if(query("devnam") == $inf_name){
			return query("uid");
		}
	}
	return "";
}

$DELIMILTER = ",";
$INF_NAME = cut($DEVICE,0,$DELIMILTER);
$MAC = cut($DEVICE,1,$DELIMILTER);
$IP = cut($DEVICE,2,$DELIMILTER);
$HOSTNAME = cut($DEVICE,3,$DELIMILTER);
$INF_UID = get_inf_uid($INF_NAME);
$UID = $INF_UID.".".$MAC;
if($CMD == "ADD"){
	if($TR069_CPE == 1){ //Come from DHCP server.
		$NAME = "ManageableDevice";
		$P_UID = "";
		if($OTHER_PARAMS != ""){
			TR069_AddObject(1,$NAME,$UID,$P_UID);
			$ManufacturerOUI = cut($OTHER_PARAMS,0,$DELIMILTER);
			$SerialNumber = cut($OTHER_PARAMS,1,$DELIMILTER);
			$ProductClass = cut($OTHER_PARAMS,2,$DELIMILTER);
			if($IP != "") TR069_SetObjectNameValue($NAME,$UID,$P_UID,"IPAddress",$IP);
			TR069_SetObjectNameValue($NAME,$UID,$P_UID,"ManufacturerOUI",$ManufacturerOUI);
			TR069_SetObjectNameValue($NAME,$UID,$P_UID,"SerialNumber",$SerialNumber);
			TR069_SetObjectNameValue($NAME,$UID,$P_UID,"ProductClass",$ProductClass);
			$TOP_ENTRY_PATH = $_GLOBALS["TR069_MULTI_BASE"]."/LANDevice/entry";
			$found = 0;
			foreach($TOP_ENTRY_PATH){
				if(query("UID") == $INF_UID) {
					$TOP_BASE_PATH = $TOP_ENTRY_PATH.":".$InDeX;
					$found = 1;
					break;
				}
			}
			if($found == 1){
				$lan_tr069_index = getattr($TOP_BASE_PATH,$_GLOBALS["TR069_INDEX_STRING"]);
				$found = 0;
				$TOP_ENTRY_PATH = $TOP_BASE_PATH."/Hosts/Host/entry";
				foreach($TOP_ENTRY_PATH){
					$HOST_UID = query("UID");
					if(strstr($HOST_UID,$MAC) != "") {
						$TOP_BASE_PATH = $TOP_ENTRY_PATH.":".$InDeX;
						$found = 1;
						break;
					}
				}
				if($found == 1){
					$host_tr069_index = getattr($TOP_BASE_PATH,$_GLOBALS["TR069_INDEX_STRING"]);
					$host = "“InternetGatewayDevice.LANDevice.".$lan_tr069_index.".Hosts.Host.".$host_tr069_index;
					TR069_SetObjectNameValue($NAME,$UID,$P_UID,"Host",$host);
				}
			}
		}
	}
	else{ //May come from DHCP server or arpmonitor.
		$NAME = "Host";
		$P_UID = $INF_UID;
		TR069_AddObject(1,$NAME,$UID,$P_UID);
		if($IP != "") TR069_SetObjectNameValue($NAME,$UID,$P_UID,"IPAddress",$IP);
		if($HOSTNAME != "") TR069_SetObjectNameValue($NAME,$UID,$P_UID,"HostName",$HOSTNAME);
	}
}
else if($CMD == "DELETE"){
	$NAME = "ManageableDevice";
	$P_UID = "";
	TR069_DeleteObject(1,$NAME,$UID,$P_UID);
	$NAME = "Host";
	$P_UID = $INF_UID;
	TR069_DeleteObject(1,$NAME,$UID,$P_UID);
}
else if($CMD == "UPDATE"){
	$NAME = "Host";
	$P_UID = $INF_UID;
	if($IP != "") TR069_SetObjectNameValue($NAME,$UID,$P_UID,"IPAddress",$IP);
	if($HOSTNAME != "") TR069_SetObjectNameValue($NAME,$UID,$P_UID,"HostName",$HOSTNAME);
}
?>