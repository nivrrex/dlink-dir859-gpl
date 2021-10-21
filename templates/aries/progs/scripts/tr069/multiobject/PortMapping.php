<?
include "/etc/scripts/tr069/phplib/io.php";
include "/etc/scripts/tr069/phplib/api.php";
include "/htdocs/phplib/inf.php";

function get_nat_uid($wan_uid)
{
	foreach("/inf"){
		if(query("uid") == $wan_uid){
			$nat_uid = query("nat");
			break;
		}
	}
	return $nat_uid;
}

function get_inet_uid($wan_uid)
{
	$inf_p = XNODE_getpathbytarget("", "inf", "uid", $wan_uid, "0");
	$inet_uid = query($inf_p."/inet");
	return $inet_uid;
}

function get_wan_type($inet_uid)
{
	$inet_p = XNODE_getpathbytarget("/inet", "entry", "uid", $inet_uid, 0);
	$wan_type = query($inet_p."/addrtype");
	return $wan_type;
}

function get_node_path($node_entry, $node_uid)
{
	foreach($node_entry){
		if(query("uid") == $node_uid){
			$node_p = $node_entry.":".$InDeX;
			break;
		}
	}
	return $node_p;
}

if($NAME != "PortMapping") return;

$WANDEV_ENTRY = $TR069_MULTI_BASE."/WANDevice/entry:".$INDEX0;
$WANCONDEV_ENTRY = $WANDEV_ENTRY."/WANConnectionDevice/entry:".$INDEX1;
$WAN_UID = query($WANCONDEV_ENTRY."/UID");
$INET_UID = get_inet_uid($WAN_UID);
$NAT_UID = get_nat_uid($WAN_UID);
$WAN_TYPE = get_wan_type($INET_UID);

if($WAN_TYPE == "ipv4")
	$TOP_PATH = $WANCONDEV_ENTRY."/WANIPConnection/entry:".$INDEX2;
else if($WAN_TYPE == "ppp4" || $WAN_TYPE == "ppp10")
	$TOP_PATH = $WANCONDEV_ENTRY."/WANPPPConnection/entry:".$INDEX2;
$BASE = $TOP_PATH."/".$NAME;
$BASE_ENTRY = $BASE."/entry:".$INDEX3;

$nat_p = XNODE_getpathbytarget("/nat", "entry", "uid", $NAT_UID, 0);
$pfwd_cnt = query($nat_p."/portforward/count");
$vsvr_cnt = query($nat_p."/virtualserver/count");
$total_portmap = $pfwd_cnt + $vsvr_cnt;

if($ACTION == "DEL"){
	$P_UID = query($TOP_PATH."/UID");
	$UID = query($BASE_ENTRY."/UID");
	TR069_DeleteObject(1,$NAME,$UID,$P_UID);
	
	if (strstr($UID, "PFWD") != "")
	{ $db_path = $nat_p. "/portforward"; }
	else if (strstr($UID, "VSVR") != "")
	{ $db_path = $nat_p. "/virtualserver"; }
	
	$path = XNODE_getpathbytarget($db_path, "entry", "uid", $UID, "0");
	if ($path != ""){
		del($path);
		$count = query($db_path."/count");
		set($db_path."/count", $count-1);
		
		fwrite("w", $EXEC_SHELL_FILE, "#!/bin/sh \n"); 
		fwrite("a", $EXEC_SHELL_FILE, "service VSVR.".$NAT_UID." restart \n"); 
		fwrite("a", $EXEC_SHELL_FILE, "service PFWD.".$NAT_UID." restart \n"); 
	}
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
			if(query($NODE_PATH) == ""){
				set($NODE_PATH,0);
				output($total_portmap);
			}
			else output(query($NODE_PATH));
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
				// Needs to implement that in port forwarding and virtual server.
				if ($SET_VALUE <= $pfwd_cnt ){ 
					$UID = "PFWD-".$SET_VALUE;
					$db_path = $nat_p."/portforward";
				}
				else{
					$index_vsvr = $SET_VALUE - $pfwd_cnt;
					$UID = "VSVR-".$index_vsvr;
					$db_path = $nat_p."/virtualserver";
				}
				TR069_AddObject(1,$NAME,$UID,$P_UID);
				if(query($TR069_STATUS_PATH) == $TR069_STATUS_RUN_STRING){
					XNODE_getpathbytarget($db_path, "entry", "uid", $P_UID, "1");
					$seqno = get ("", $db_path."/seqno");
					set($db_path."/seqno", $seqno+1);
					$count = get ("",  $db_path."/count");
					set($db_path."/count", $count+1);
				}
			}
		}
	}
	else if($PARAM_NAME == $TR069_PATH_STRING){
		$NODE_PATH = $BASE_ENTRY;
		if($ACTION == "GET") output(getattr($NODE_PATH,$TR069_PATH_STRING));
		else if($ACTION == "SET") setattr($NODE_PATH,$TR069_PATH_STRING,$SET_VALUE);
	}
	else{
		if(strstr($PARAM_NAME, "PortMapping.") != ""){
			$NEXT_PARAM_NAME = cut($PARAM_NAME,1,".");
			
			$port_map_uid = query($BASE_ENTRY."/UID");
			if(strstr($port_map_uid, "PFWD") != ""){
				$NODE_ENTRY = $nat_p."/portforward/entry";
			}
			else if(strstr($port_map_uid, "VSVR") != ""){
				$NODE_ENTRY = $nat_p."/virtualserver/entry";
			}
			$NODE_BASE = get_node_path($NODE_ENTRY, $port_map_uid);
			
			if($NEXT_PARAM_NAME == "PortMappingEnabled"){
				exec_by_type($ACTION, $NODE_BASE."/enable", $SET_VALUE);
			}
			else if($NEXT_PARAM_NAME == "PortMappingLeaseDuration"){ //Not support
				if ($ACTION == "GET"){
					output("0");
				}
			}
			else if($NEXT_PARAM_NAME == "RemoteHost"){
				$NODE_PATH = $NODE_BASE."/internal/hostid";
				$internal_inf = query($NODE_BASE."/internal/inf");
				
				if ($ACTION == "GET"){
					$lanip = INF_getcurripaddr($internal_inf);
					$mask = INF_getcurrmask($internal_inf);
					$hostid = query($NODE_PATH);
					$ipv4addr = ipv4ip($lanip, $mask, $hostid);
					output($ipv4addr);
				}
				else if ($ACTION == "SET"){
					$ipv4addr = $SET_VALUE;
					$mask = INF_getcurrmask($internal_inf);
					$hostid = ipv4hostid($ipv4addr, $mask);
					set($NODE_PATH, $hostid);
				}
				else if ($ACTION == "GET_PATH"){ output($NODE_PATH); }
			}
			else if($NEXT_PARAM_NAME == "ExternalPort"){
				exec_by_type($ACTION, $NODE_BASE."/external/start", $SET_VALUE);
			}
			else if($NEXT_PARAM_NAME == "InternalPort"){
				exec_by_type($ACTION, $NODE_BASE."/internal/start", $SET_VALUE);
			}
			else if($NEXT_PARAM_NAME == "PortMappingProtocol"){
				exec_by_type($ACTION, $NODE_BASE."/protocol", $SET_VALUE);
			}
			else if($NEXT_PARAM_NAME == "InternalClient"){
				$internal_inf = query($NODE_BASE."/internal/inf");
				if ($ACTION == "GET"){
					$lanip = INF_getcurripaddr($internal_inf);
					output($lanip);
				}
				else if($ACTION == "SET"){
					$lan_inf_p = XNODE_getpathbytarget("", "inf", "uid", $internal_inf, 0 );
					$lan_inet = query($lan_inf_p."/inet");
					$lan_inet_p = XNODE_getpathbytarget("/inet", "entry", "uid", $lan_inet, 0);
					set($lan_inet_p."/ipv4/ipaddr", $SET_VALUE);
				}
			}
			else if($NEXT_PARAM_NAME == "PortMappingDescription"){
				exec_by_type($ACTION, $NODE_BASE."/description", $SET_VALUE);
			}
		}
		else
			TRACE_error("Not implemented yet. $PARAM_NAME=".$PARAM_NAME);
		
		if ($ACTION == "SET"){
			fwrite("w", $EXEC_SHELL_FILE, "#!/bin/sh \n"); 
			fwrite("a", $EXEC_SHELL_FILE, "service VSVR.".$NAT_UID." restart \n"); 
			fwrite("a", $EXEC_SHELL_FILE, "service PFWD.".$NAT_UID." restart \n"); 
		}
	}
}
?>