<?
include "/etc/scripts/tr069/phplib/io.php";
include "/etc/scripts/tr069/phplib/api.php";

if($NAME != "IPInterface") return;

$TOP_PATH = $TR069_MULTI_BASE."/LANDevice/entry:".$INDEX0;
$BASE = $TOP_PATH."/LANHostConfigManagement/".$NAME;
$BASE_ENTRY = $BASE."/entry:".$INDEX1;

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
				output(query($TRBASE."/landevicenumberofentries"));
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
			output(getattr($NODE_PATH,$TR069_INDEX_STRING));
		else if($ACTION == "SET"){
			setattr($NODE_PATH,$TR069_INDEX_STRING,$SET_VALUE);
			if(query($BASE_ENTRY."/UID") == ""){ //Note: Avoid a wrong loop
				$P_UID = query($TOP_PATH."/UID");
				$UID = $P_UID.".IPI-".$SET_VALUE;
				TR069_AddObject(1, $NAME, $UID, $P_UID);
			}
		}
	}
	else if($PARAM_NAME == $TR069_PATH_STRING){
		$NODE_PATH = $BASE_ENTRY;
		if($ACTION == "GET") output(getattr($NODE_PATH,$TR069_PATH_STRING));
		else if($ACTION == "SET") setattr($NODE_PATH,$TR069_PATH_STRING,$SET_VALUE);
	}
	else{
		if(strstr($PARAM_NAME, "IPInterface.") != ""){
			$NEXT_PARAM_NAME = cut($PARAM_NAME,1,".");
			$LAN_UID = query($TOP_PATH."/UID");
			
			$inf_p = XNODE_getpathbytarget("", "inf", "uid", $LAN_UID,"0");
			$inet = query($inf_p."/inet");
			$inet_p = XNODE_getpathbytarget("/inet", "entry", "uid", $inet, 0);
			
			$mask = query($inet_p."/ipv4/mask");
			
			if($NEXT_PARAM_NAME == "Enable"){
				exec_by_type($ACTION, $inf_p."/active", $SET_VALUE);
			}
			else if($NEXT_PARAM_NAME == "IPInterfaceIPAddress"){
				exec_by_type($ACTION, $inet_p."/ipv4/ipaddr", $SET_VALUE);
			}
			else if($NEXT_PARAM_NAME == "IPInterfaceSubnetMask"){
				$NODE_PATH = $inet_p."/ipv4/mask";
				if($ACTION == "GET"){
					$mask = query($NODE_PATH);
					output(ipv4int2mask($mask));
				}
				else if($ACTION == "SET"){
					set($NODE_PATH, ipv4mask2int($SET_VALUE));
				}
				else if($ACTION == "GET_PATH"){
					output($NODE_PATH);
				}
			}
			else if($NEXT_PARAM_NAME == "IPInterfaceAddressingType"){
				if($ACTION == "GET"){ /*In current design, it is always static.*/
					output("Static");
				}
			}
			
			if ($ACTION == "SET")
			{
				fwrite("w", $EXEC_SHELL_FILE, "#!/bin/sh \n"); 
				fwrite("a", $EXEC_SHELL_FILE, "service INET.".$LAN_UID." restart \n"); 
			}
		}
	}
}
?>