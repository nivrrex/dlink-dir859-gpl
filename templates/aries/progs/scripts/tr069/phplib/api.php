<?
include "/etc/scripts/tr069/phplib/env.php";

function __CreateEntity($base,$num,$uid_list)
{
	$success = 0;
	$i = 1;
	while($i <= $num){
		$found = 0;
		$uid = cut($uid_list,$i-1," ");
		if($uid != ""){
			foreach($base."/entry"){
				if(query("UID") == $uid) {
					$found = 1;
					break;
				}
			}
			if($found != 1){
				$cnt = query($base."/".$_GLOBALS["TR069_COUNT_STRING"]);
				if($cnt == "") $cnt = 0;
				$cnt += 1;
				set($base."/".$_GLOBALS["TR069_COUNT_STRING"],$cnt);
				set($base."/entry:".$cnt."/UID",$uid);
				$success = 1;
			}
		}
		$i++;
	}
	return $success;
}

function __DestroyEntity($base,$num,$uid_list)
{
	$success = 0;
	$i = 1;
	while($i <= $num){
		$found = 0;
		$uid = cut($uid_list,$i-1," ");
		if($uid != ""){
			foreach($base."/entry"){
				if(query("UID") == $uid) {
					$index = $InDeX;
					$found = 1;
					break;
				}
			}
			if($found == 1){
				$cnt = query($base."/".$_GLOBALS["TR069_COUNT_STRING"]);
				if($cnt > 0){
					$cnt -= 1;
					del($base."/entry:".$index);
					set($base."/".$_GLOBALS["TR069_COUNT_STRING"],$cnt);
					$success = 1;
				}
			}
		}
		$i++;
	}
	return $success;
}

function __FindPath($multiobj_type, $PUID)
{
	$found = 0;
	/*
	Level 1
	InternetGatewayDevice.Layer3Forwarding.Forwarding.{i}.
	InternetGatewayDevice.WANDevice.{i}.
	InternetGatewayDevice.LANDevice.{i}.
	InternetGatewayDevice.ManagementServer.ManageableDevice.{i}.
	*/
	if($multiobj_type == "Forwarding" || $multiobj_type == "LANDevice" || 
		$multiobj_type == "WANDevice" || $multiobj_type == "ManageableDevice"){
		$found = 1;
		$BASE = $_GLOBALS["TR069_MULTI_BASE"]."/".$multiobj_type;
	}
	/*
	Level 2
	InternetGatewayDevice.WANDevice.{i}.WANConnectionDevice.{i}.
	InternetGatewayDevice.LANDevice.{i}.LANHostConfigManagement.IPInterface.{i}.
	InternetGatewayDevice.LANDevice.{i}.LANEthernetInterfaceConfig.{i}.
	InternetGatewayDevice.LANDevice.{i}.Hosts.Host.{i}.
	InternetGatewayDevice.LANDevice.{i}.WLANConfiguration.{i}.
	*/
	else if($multiobj_type == "WANConnectionDevice"){
		$TOP_ENTRY_PATH = $_GLOBALS["TR069_MULTI_BASE"]."/WANDevice/entry";
		foreach($TOP_ENTRY_PATH){
			if(query("UID") == $PUID) {
				$index1 = $InDeX;
				$found = 1;
				break;
			}
		}
		if($found == 1){
			$TOP_PATH = $_GLOBALS["TR069_MULTI_BASE"]."/WANDevice/entry:".$index1;
			$BASE = $TOP_PATH."/".$multiobj_type;
		}
	}
	else if($multiobj_type == "IPInterface" || $multiobj_type == "LANEthernetInterfaceConfig" || $multiobj_type == "Host" || $multiobj_type == "WLANConfiguration"){
		$TOP_ENTRY_PATH = $_GLOBALS["TR069_MULTI_BASE"]."/LANDevice/entry";
		foreach($TOP_ENTRY_PATH){
			if(query("UID") == $PUID) {
				$index1 = $InDeX;
				$found = 1;
				break;
			}
		}
		if($found == 1){
			$TOP_PATH = $_GLOBALS["TR069_MULTI_BASE"]."/LANDevice/entry:".$index1;
			if($multiobj_type == "IPInterface"){
				$BASE = $TOP_PATH."/LANHostConfigManagement/".$multiobj_type;
			}
			else if($multiobj_type == "Host"){
				$BASE = $TOP_PATH."/Hosts/".$multiobj_type;
			}
			else{
				$BASE = $TOP_PATH."/".$multiobj_type;
			}
		}
	}
	/*
	Level 3
	InternetGatewayDevice.WANDevice.{i}.WANConnectionDevice.{i}.WANIPConnection.{i}.
	InternetGatewayDevice.WANDevice.{i}.WANConnectionDevice.{i}.WANPPPConnection.{i}.
	*/
	else if($multiobj_type == "WANIPConnection" || $multiobj_type == "WANPPPConnection"){
		$TOP_LEVEL1_ENTRY_PATH = $_GLOBALS["TR069_MULTI_BASE"]."/WANDevice/entry";
		foreach($TOP_LEVEL1_ENTRY_PATH){
			$index1 = $InDeX;
			$TOP_LEVEL2_ENTRY_PATH = $TOP_LEVEL1_ENTRY_PATH.":".$index1."/WANConnectionDevice/entry";
			foreach($TOP_LEVEL2_ENTRY_PATH){
				if(query("UID") == $PUID) {
					$index2 = $InDeX;
					$found = 1;
					break;
				}
			}
			if($found == 1) break; 
		}
		if($found == 1){
			$TOP_PATH = $TOP_LEVEL2_ENTRY_PATH.":".$index2;
			$BASE = $TOP_PATH."/".$multiobj_type;
		}
	}
	/*
	Level 4
	InternetGatewayDevice.WANDevice.{i}.WANConnectionDevice.{i}.WANPPPConnection.{i}.PortMapping.{i}.
	InternetGatewayDevice.WANDevice.{i}.WANConnectionDevice.{i}.WANIPConnection.{i}.PortMapping.{i}.
	*/
	else if($multiobj_type == "PortMapping"){
		$TOP_LEVEL1_ENTRY_PATH = $_GLOBALS["TR069_MULTI_BASE"]."/WANDevice/entry";
		foreach($TOP_LEVEL1_ENTRY_PATH){
			$index1 = $InDeX;
			$TOP_LEVEL2_ENTRY_PATH = $TOP_LEVEL1_ENTRY_PATH.":".$index1."/WANConnectionDevice/entry";
			foreach($TOP_LEVEL2_ENTRY_PATH){
				$index2 = $InDeX;
				if(strstr($PUID,"PPP") != "") 
					$TOP_LEVEL3_ENTRY_PATH = $TOP_LEVEL2_ENTRY_PATH.":".$index2."/WANPPPConnection/entry";
				else
					$TOP_LEVEL3_ENTRY_PATH = $TOP_LEVEL2_ENTRY_PATH.":".$index2."/WANIPConnection/entry";
				
				foreach($TOP_LEVEL3_ENTRY_PATH){
					if(query("UID") == $PUID) {
						$index3 = $InDeX;
						$found = 1;
						break;
					}
				}
				if($found == 1) break; 	
			}
			if($found == 1) break; 	
		}
		if($found == 1){
			$TOP_PATH = $TOP_LEVEL3_ENTRY_PATH.":".$index3;
			$BASE = $TOP_PATH."/".$multiobj_type;
		}
	}
	else
		TRACE_error("Unknown type : ".$multiobj_type);
		
	if($found == 1) return $BASE;
	else			return "";
}

function IsTR069Support()
{
	if(isfile("/usr/sbin/tr069c") == 1) return 1;
	else								return 0;
}

function TR069_AddObject($num,$multiobj_type,$UID_list, $PUID)
{
	$success = 0;
	if(IsTR069Support() != 1) return $success;
	$Object_path = __FindPath($multiobj_type,$PUID);
	if($Object_path != "") $success = __CreateEntity($Object_path,$num,$UID_list);
	
	return $success;
}

function TR069_DeleteObject($num,$multiobj_type,$UID_list, $PUID)
{
	$success = 0;
	if(IsTR069Support() != 1) return $success;
	$Object_path = __FindPath($multiobj_type,$PUID);
	if($Object_path != "") $success = __DestroyEntity($Object_path,$num,$UID_list);
	
	return $success;
}

function TR069_SetObjectNameValue($multiobj_type,$UID,$PUID,$name,$value)
{
	$success = 0;
	if(IsTR069Support() != 1) return $success;
	$path = __FindPath($multiobj_type,$PUID);
	if($path != ""){
		$found = 0;
		$entry_path = $path."/entry";
		foreach($entry_path){
			if(query("UID") == $UID) {
				$base = $entry_path.":".$InDeX;
				$found = 1;
				break;
			}
		}
		if($found == 1) {
			set($base."/".$name,$value);
			$success = 1;
		}
	}
	return $success;
}
?>