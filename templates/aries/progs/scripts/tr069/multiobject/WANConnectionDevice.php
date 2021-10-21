<?
include "/etc/scripts/tr069/phplib/io.php";
include "/etc/scripts/tr069/phplib/api.php";

if($NAME != "WANConnectionDevice") return;

$TOP_PATH = $TR069_MULTI_BASE."/WANDevice/entry:".$INDEX0;
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
		if($ACTION == "GET")  {
			if(query($NODE_PATH) == ""){
				set($NODE_PATH,0);
				$P_UID = query($TOP_PATH."/UID");
				$cnt = 0;
				foreach("/inf"){
					$WAN_UID = query("uid");
					if(strstr($WAN_UID,"WAN") != "" && query("name") == ""){
						if(query("phyinf") == $P_UID){
							if(query("active") == 1) $cnt++;
						}
					}
				}
				output($cnt);
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
				foreach("/inf"){
					$WAN_UID = query("uid");
					if(strstr($WAN_UID,"WAN") != "" && query("name") == ""){
						if(query("phyinf") == $P_UID){
							$success = TR069_AddObject(1,$NAME,$WAN_UID,$P_UID);
							if($success == 1) break;
						}
					}
				}
			}
		}
	}
	else if($PARAM_NAME == $TR069_PATH_STRING){
		$NODE_PATH = $BASE_ENTRY;
		if($ACTION == "GET") output(getattr($NODE_PATH,$TR069_PATH_STRING));
		else if($ACTION == "SET") setattr($NODE_PATH,$TR069_PATH_STRING,$SET_VALUE);
	}
	else if($PARAM_NAME == "wan_mode"){
		$WAN_UID = query($BASE_ENTRY."/UID");
		$inf_runtime_p = XNODE_getpathbytarget("/runtime", "inf", "uid", $WAN_UID, 0);
		$inet_p = XNODE_getpathbytarget("/inet", "entry", "uid", query($inf_runtime_p."/inet/uid"), 0);
		anchor($inet_p);
		
		$mode=query("addrtype");
		if($mode == "ipv4"){
			if(query("ipv4/static") == 1) //Static
				output("static");
			else
				output("dhcp");
		}
		else if($mode == "ppp4" && query("ppp4/over") == "eth"){
			//PPPoE
			output("pppoe");
		}
		else if($mode == "ppp4" && query("ppp4/over") == "pptp"){
			//PPTP
			output("pptp");
		}
		else if($mode == "ppp4" && query("ppp4/over") == "l2tp"){
			//L2TP
			output("l2tp");
		}
	}
	else{
		if(strstr($PARAM_NAME, "WANConnectionDevice.") != ""){
			$NEXT_PARAM_NAME = cut($PARAM_NAME,1,".");
			if($NEXT_PARAM_NAME == "WANIPConnectionNumberOfEntries"){
				$NODE_PATH = $BASE_ENTRY."/WANIPConnection/".$TR069_COUNT_STRING;
				exec_by_type($ACTION,$NODE_PATH,$SET_VALUE);
			}
			else if($NEXT_PARAM_NAME == "WANPPPConnectionNumberOfEntries"){
				$NODE_PATH = $BASE_ENTRY."/WANPPPConnection/".$TR069_COUNT_STRING;
				exec_by_type($ACTION,$NODE_PATH,$SET_VALUE);
			}
		}
		else if(strstr($PARAM_NAME, "WANEthernetLinkConfig.") != ""){
			$NEXT_PARAM_NAME = cut($PARAM_NAME,1,".");
			if($NEXT_PARAM_NAME == "EthernetLinkStatus"){
				$P_UID = query($TOP_PATH."/UID");
				$phyinf_runtime_p = XNODE_getpathbytarget("/runtime","phyinf","uid",$P_UID,0);
				$NODE_PATH = $phyinf_runtime_p."/linkstatus";
				if($ACTION == "GET"){
					$phyinf_p = XNODE_getpathbytarget("","phyinf", "uid", $P_UID, "0");
					if(query($phyinf_p."/active") != 1) output("Unavailable");
					else{
						if(query($NODE_PATH) == 1) output("Up");
						else					   output("Down");
					}
				}
				else
					exec_by_type($ACTION,$NODE_PATH,$SET_VALUE);
			}
		}
		else
			TRACE_error("Not implemented yet. $PARAM_NAME=".$PARAM_NAME);
	}
}
?>