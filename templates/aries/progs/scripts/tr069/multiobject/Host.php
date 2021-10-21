<?
include "/etc/scripts/tr069/phplib/io.php";
include "/etc/scripts/tr069/phplib/api.php";

function get_address_source($entry, $mac_addr)
{
	$source = "Static";
	foreach($entry){
		if(query("macaddr") == $mac_addr){
			$source = "DHCP";
			break;
		}
	}
	return $source;
}

function get_due_time($entry, $mac_addr)
{
	$due_time = "";
	foreach($entry){
		if(query("macaddr") == $mac_addr){
			$due_time = query("due_time");
			break;
		}
	}
	return $due_time;
}

function check_ip_interface($lan_uid, $interface)
{
	$base = "/runtime/inf";
	foreach($base){
		if(query("uid") == $lan_uid){
			if(query("devnam") == $interface) return true;
			else							  return false;
		}
	}
	return false;
}

function is_wifi_client($phyinf, $mac_addr)
{
	$phyinf_runtime_p = XNODE_getpathbytarget("/runtime", "phyinf", "uid", $phyinf, "0");
	$wifi_client = false;
	foreach($phyinf_runtime_p."/media/clients/entry"){
		$wifi_client_mac = query("macaddr");
		if($mac_addr == tolower($wifi_client_mac)){
			$wifi_client = true;
			break;
		}
	}
	return $wifi_client;
}

if($NAME != "Host") return;

$TOP_PATH = $TR069_MULTI_BASE."/LANDevice/entry:".$INDEX0;
$BASE = $TOP_PATH."/Hosts/".$NAME;
$BASE_ENTRY = $BASE."/entry:".$INDEX1;
$NODE_ENTRY = "/runtime/mydlink/userlist/entry";

if($ACTION == "DEL"){
	$UID = query($BASE_ENTRY."/UID");
	TR069_DeleteObject(1,$NAME,$UID,"");
}
else if($ACTION == "GET_INDEX"){
	$entry_index = 0;
	$instance_num = $INSTANCE;
	foreach($BASE."/entry"){
		$tr069_index = getattr("",$TR069_INDEX_STRING);
		if($tr069_index == $instance_num){
			$entry_index = $InDeX;
			break;
		}
	}
	output($entry_index);
}
else{
	if($PARAM_NAME == $TR069_COUNT_STRING){
		$NODE_PATH = $BASE."/".$TR069_COUNT_STRING;
		
		if($ACTION == "GET") {
			if(query($NODE_PATH) == "") {
				set($NODE_PATH,0);
				output(query($NODE_ENTRY."#"));
			}
			else
				output(query($NODE_PATH));
		}
		else if($ACTION == "SET") set($NODE_PATH,$SET_VALUE);
		else if($ACTION == "GET_PATH") output($NODE_PATH);
	}
	else if($PARAM_NAME == $TR069_SEQ_STRING){
		$NODE_PATH = $BASE;
		if($ACTION == "GET")
			output(getattr($NODE_PATH,$TR069_SEQ_STRING));
		else if($ACTION == "SET")
			setattr($NODE_PATH,$TR069_SEQ_STRING,$SET_VALUE);
	}
	else if($PARAM_NAME == $TR069_INDEX_STRING){
		$NODE_PATH = $BASE_ENTRY;
		if($ACTION == "GET")
			output(getattr($NODE_PATH, $TR069_INDEX_STRING));
		else if($ACTION == "SET"){
			setattr($NODE_PATH,$TR069_INDEX_STRING,$SET_VALUE);
			if(query($BASE_ENTRY."/UID") == ""){ //Note: Avoid a wrong loop
				$LAN_UID = query($TOP_PATH."/UID");
				foreach($NODE_ENTRY){
					if($InDeX >= $INDEX1){
						$infname = query("infname");
						if(check_ip_interface($LAN_UID, $infname) == true){
							$index = $InDeX;
							break;
						}
					}
				}
				$NODE_BASE = $NODE_ENTRY.":".$index;
				$UID = $LAN_UID.".".query($NODE_BASE."/macaddr");
				$P_UID = $LAN_UID;
				TR069_AddObject(1, $NAME, $UID, $P_UID);
				TR069_SetObjectNameValue($NAME,$UID,$P_UID,"IPAddress",query($NODE_BASE."/ipv4addr"));
				TR069_SetObjectNameValue($NAME,$UID,$P_UID,"HostName",query($NODE_BASE."/hostname"));
			}
		}
	}
	else if($PARAM_NAME == $TR069_PATH_STRING){
		$NODE_PATH = $BASE_ENTRY;
		if($ACTION == "GET") output(getattr($NODE_PATH,$TR069_PATH_STRING));
		else if($ACTION == "SET") setattr($NODE_PATH,$TR069_PATH_STRING,$SET_VALUE);
	}
	else{
		if(strstr($PARAM_NAME, "Host.") != ""){
			$NEXT_PARAM_NAME = cut($PARAM_NAME,1,".");
			$LAN_UID = query($TOP_PATH."/UID");
			$HOST_UID = query($BASE_ENTRY."/UID");
			$MAC = cut($HOST_UID,1,".");
			$inf_runtime_p = XNODE_getpathbytarget("/runtime","inf", "uid", $LAN_UID, 0);
			
			if($NEXT_PARAM_NAME == "IPAddress"){
				$NODE_PATH = $BASE_ENTRY."/IPAddress";
				exec_by_type($ACTION, $NODE_PATH, $SET_VALUE);
			}
			else if($NEXT_PARAM_NAME == "AddressSource"){
				if($ACTION == "GET"){
					$addr_source = get_address_source($inf_runtime_p."/dhcps4/leases/entry", $MAC);
					output($addr_source);
				}
			}
			else if($NEXT_PARAM_NAME == "LeaseTimeRemaining"){
				if($ACTION == "GET"){
					$duetime = get_due_time($inf_runtime_p."/dhcps4/leases/entry", $MAC);
					if($duetime == ""){ output("0"); }
					else{ output($duetime); }
				}
			}
			else if($NEXT_PARAM_NAME == "MACAddress"){
				$NODE_PATH = $BASE_ENTRY."/UID";
				if($ACTION == "GET"){
					output($MAC);
				}
				else
					exec_by_type($ACTION, $NODE_PATH, $SET_VALUE);
			}
			else if($NEXT_PARAM_NAME == "HostName"){
				$NODE_PATH = $BASE_ENTRY."/HostName";
				exec_by_type($ACTION, $NODE_PATH, $SET_VALUE);
			}
			else if($NEXT_PARAM_NAME == "InterfaceType"){
				if($ACTION == "GET"){
					$is_24g = is_wifi_client($WLAN1, $MAC);
					$is_5g = is_wifi_client($WLAN2, $MAC);
					$is_24g_guest = is_wifi_client($WLAN1_GZ, $MAC);
					$is_5g_guest = is_wifi_client($WLAN2_GZ, $MAC);
					
					if($is_24g == true || $is_5g == true || $is_24g_guest == true || $is_5g_guest == true){
						output("802.11");
					}
					else{
						output("Ethernet");
					}
				}
			}
			else if($NEXT_PARAM_NAME == "Active"){
				if($ACTION == "GET"){
					output("1");
				}
			}
		}
	}
}
?>