<?
include "/etc/scripts/tr069/phplib/io.php";
include "/etc/scripts/tr069/phplib/api.php";

if($NAME != "LANEthernetInterfaceConfig") return;

$TOP_PATH = $TR069_MULTI_BASE."/LANDevice/entry:".$INDEX0;
$BASE = $TOP_PATH."/".$NAME;
$BASE_ENTRY = $BASE."/entry:".$INDEX1;
if($ACTION == "DEL"){
	$P_UID = query($TOP_PATH."/UID");
	$UID = query($BASE_ENTRY."/UID");
	TR069_DeleteObject(1,$NAME,$UID,$P_UID);
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
				output(query($TRBASE."/lanethernetinterfacenumberofentries"));
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
				$UID = "ETHCFG-".$SET_VALUE;
				TR069_AddObject(1,$NAME,$UID,$P_UID);
			}
		}
	}
	else if($PARAM_NAME == $TR069_PATH_STRING){
		$NODE_PATH = $BASE_ENTRY;
		if($ACTION == "GET") output(getattr($NODE_PATH,$TR069_PATH_STRING));
		else if($ACTION == "SET") setattr($NODE_PATH,$TR069_PATH_STRING,$SET_VALUE);
	}
	else{
		$NEXT_PARAM_NAME = cut($PARAM_NAME,1,".");
		if($NEXT_PARAM_NAME == "Enable"){
			//No Support
		}
		else if($NEXT_PARAM_NAME == "Status"){
			$LAN_PHY_UID = query($TOP_PATH."/UID");
			$path_run_phyinf = XNODE_getpathbytarget("/runtime", "phyinf", "uid", $LAN_PHY_UID, 0);
			$NODE_PATH = $path_run_phyinf."/linkstatus:".$INDEX1;
			if($ACTION == "GET"){
				$linkstatus = query($NODE_PATH);
				if($linkstatus == 1) output("Up");
				else				 output("NoLink");
			}
			else if($ACTION == "SET"){
				//No Support
			}
			else if($ACTION == "GET_PATH"){
				output($NODE_PATH);
			}
		}
		else if($NEXT_PARAM_NAME == "Name" || $NEXT_PARAM_NAME == "MACAddress"){
			if($NEXT_PARAM_NAME == "Name")
				$type = "name";
			else if($NEXT_PARAM_NAME == "MACAddress")
				$type = "macaddr";
			else
				return;
			$LAN_PHY_UID = query($TOP_PATH."/UID");
			$path_run_phyinf = XNODE_getpathbytarget("/runtime", "phyinf", "uid", $LAN_PHY_UID, 0);
			$NODE_PATH = $path_run_phyinf."/".$type;
			if($ACTION == "GET"){
				output(query($NODE_PATH));
			}
			else if($ACTION == "SET"){
				//No Support
			}
			else if($ACTION == "GET_PATH"){
				output($NODE_PATH);
			}
		}
		else if($NEXT_PARAM_NAME == "MACAddressControlEnabled"){
			$NODE_PATH = "/acl/macctrl/policy";
			if($ACTION == "GET"){
				$policy = query($NODE_PATH);
				if($policy == "" || $policy == "DISABLE") output("0");
				else									  output("1");
			}
			else if($ACTION == "SET"){
				if($SET_VALUE == "1" || tolower($SET_VALUE) == "true") set($NODE_PATH,"ACCEPT");
				else												   set($NODE_PATH,"DISABLE");
				
			}
			else if($ACTION == "GET_PATH"){
				output($NODE_PATH);
			}
		}
		else if($NEXT_PARAM_NAME == "MaxBitRate" || $NEXT_PARAM_NAME == "DuplexMode"){
			//No Support
		}
		else{
			$LAN_PHY_UID = query($TOP_PATH."/UID");
			$path_run_phyinf = XNODE_getpathbytarget("/runtime", "phyinf", "uid", $LAN_PHY_UID, 0);
			$phy_name = query($path_run_phyinf."/name");
			if($PARAM_NAME == "EthernetBytesSent")
				$type = "tx_bytes";
			else if($PARAM_NAME == "EthernetBytesReceived")
				$type = "rx_bytes";
			else if($PARAM_NAME == "EthernetPacketsSent")
				$type = "tx_packets";
			else if($PARAM_NAME == "EthernetPacketsReceived")
				$type = "rx_packets";
			else if($PARAM_NAME == "EthernetErrorsSent")
				$type = "tx_errors";
			else if($PARAM_NAME == "EthernetErrorsReceived")
				$type = "rx_errors";
			if($ACTION == "GET"){
				if($type != "") output(query("/sys/class/net/".$phy_name."/statistics/".$type));
				else			output("0");
			}
		}
		if ($ACTION == "SET")
		{
			fwrite("w", $EXEC_SHELL_FILE, "#!/bin/sh \n"); 
			fwrite("a", $EXEC_SHELL_FILE, "service MACCTRL restart \n"); 
			$LAN_PHY_UID = query($TOP_PATH."/UID");
			fwrite("a", $EXEC_SHELL_FILE, "service PHYINF.".$LAN_PHY_UID." restart \n"); 
		}
	}
}
?>