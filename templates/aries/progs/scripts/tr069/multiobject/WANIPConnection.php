<?
include "/etc/scripts/tr069/phplib/io.php";
include "/etc/scripts/tr069/phplib/api.php";

// InternetGatewayDevice.WANDevice.{i}.WANConnectionDevice.{i}.WANIPConnection.{i}.
function getPhyinfPath($wan_uid)
{
	$inf_path = XNODE_getpathbytarget("/runtime", "inf", "uid", $wan_uid, "0");
	$phyinf = query($inf_path. "/phyinf");
	$phyinf_path = XNODE_getpathbytarget ("/runtime", "phyinf", "uid", $phyinf, "0");
	return $phyinf_path;
}

if($NAME != "WANIPConnection") return;

$TOP_PATH = $TR069_MULTI_BASE."/WANDevice/entry:".$INDEX0."/WANConnectionDevice/entry:".$INDEX1;
$BASE = $TOP_PATH."/".$NAME;
$BASE_ENTRY = $BASE."/entry:".$INDEX2;
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
			if(query($NODE_PATH) == ""){
				set($NODE_PATH,0);
				$inf_runtime_p = XNODE_getpathbytarget("/runtime", "inf", "uid", query($TOP_PATH."/UID"), 0);
				$inf_inet_runtime_p = $inf_runtime_p."/inet";
				$cnt = 0;
				foreach($inf_inet_runtime_p){
					if(query("addrtype") == "ipv4") $cnt++;
				}
				output($cnt);
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
				$inf_runtime_p = XNODE_getpathbytarget("/runtime", "inf", "uid", $P_UID, 0);
				$inf_inet_runtime_p = $inf_runtime_p."/inet";
				foreach($inf_inet_runtime_p){
					if(query("addrtype") == "ipv4"){
						$UID = query("uid");
						$success = TR069_AddObject(1,$NAME,$UID,$P_UID);
						if($success == 1) break;
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
	else{
		$WAN_UID = query($TOP_PATH."/UID");
		$INET_UID = query($BASE_ENTRY."/UID");
		$path_wan_inet = XNODE_getpathbytarget("/inet","entry", "uid", $INET_UID, "0");
		$inf_path = XNODE_getpathbytarget("", "inf", "inet", $INET_UID, "0");
		
		$NEXT_PARAM_NAME = cut($PARAM_NAME,1,".");
		if($NEXT_PARAM_NAME == "AddressingType"){
			if(query($path_wan_inet."/addrtype") == "ipv4"){
				$NODE_PATH = $path_wan_inet."/ipv4/static";
				if($ACTION == "GET"){
					if(query($NODE_PATH) == 1) //Static
						output("Static");
					else
						output("DHCP");
				}
				else if($ACTION == "SET"){
					if($SET_VALUE == "Static")
						set($NODE_PATH,1);
					else
						set($NODE_PATH,0);
				}
				else if($ACTION == "GET_PATH")
					output($NODE_PATH);
			}
		}
		else if($NEXT_PARAM_NAME == "ExternalIPAddress" || $NEXT_PARAM_NAME == "SubnetMask" || $NEXT_PARAM_NAME == "DefaultGateway"){
			if($NEXT_PARAM_NAME == "ExternalIPAddress")
				$type = "ipaddr";
			else if($NEXT_PARAM_NAME == "SubnetMask")
				$type = "mask";
			else if($NEXT_PARAM_NAME == "DefaultGateway")
				$type = "gateway";
				
			if(query($path_wan_inet."/addrtype") == "ipv4"){
				if(query($path_wan_inet."/ipv4/static") == 1) { //Static
					$NODE_PATH = $path_wan_inet."/ipv4/".$type;
					if($ACTION == "GET"){
						if($NEXT_PARAM_NAME == "SubnetMask")
							output(ipv4int2mask(query($NODE_PATH)));
						else
							output(query($NODE_PATH));
					}
					else if($ACTION == "SET")
						set($NODE_PATH,$SET_VALUE);
					else if($ACTION == "GET_PATH")
						output($NODE_PATH);
				}
				else{ //DHCP
					$path_run_inf_wan = XNODE_getpathbytarget("/runtime", "inf", "uid", $WAN_UID, 0);
					$path_run_inet_wan = XNODE_getpathbytarget($path_run_inf_wan, "inet", "uid", $INET_UID, 0);
					$NODE_PATH = $path_run_inet_wan."/ipv4/".$type;
					if($ACTION == "GET"){
						if($NEXT_PARAM_NAME == "SubnetMask")
							output(ipv4int2mask(query($NODE_PATH)));
						else
							output(query($NODE_PATH));
					}
					else if($ACTION == "GET_PATH")
						output($NODE_PATH);
				}
			}
		}
		else if ($NEXT_PARAM_NAME == "Enable")
		{ 
			exec_by_type($ACTION, $inf_path."/active", $SET_VALUE); 
		}
		else if ($NEXT_PARAM_NAME == "ConnectionStatus")
		{ 
			if ($ACTION == "GET")
			{
				$active = query($inf_path."/active");
				if ($active == "0")
				{ output("Unconfigured"); }
				else
				{
					$addrtype = get("",$path_wan_inet."/ipv4/static");
					if ($addrtype == "1")	// if addrtype is static, always return Connected.
						{ output("Connected");}
					else if ($addrtype == "0")
					{
						$inf_r_p = XNODE_getpathbytarget("/runtime", "inf", "uid", $WAN_UID, "0");
						if (get("",$inf_r_p."/inet/ipv4/ipaddr") != "")
							{ output ("Connected"); }
						else
							{ output ("Connecting"); }	// If device does not get IP, reply with connecting.
					}
				}
			}
		}
		else if ($NEXT_PARAM_NAME == "PossibleConnectionTypes")
		{
			if ($ACTION == "GET") { output ("IP_Routed"); }
		}
		else if ($NEXT_PARAM_NAME == "ConnectionType")
		{
			if ($ACTION == "GET") { output ("IP_Routed"); }
		}
		else if ($NEXT_PARAM_NAME == "Name")
		{
			$NODE_PATH = $BASE_ENTRY."/name";
			exec_by_type($ACTION, $NODE_PATH, $SET_VALUE);
		}
		else if ($NEXT_PARAM_NAME == "Uptime")
		{
			if ($ACTION == "GET")
			{
				$inf_r_p = XNODE_getpathbytarget("/runtime", "inf", "uid", $WAN_UID, "0");
				$wan_uptime = get ("",$inf_r_p."/inet/uptime");
				$device_uptime = query("/runtime/device/uptime");
				output ($device_uptime - $wan_uptime);
			}
		}
		else if ($NEXT_PARAM_NAME == "LastConnectionError")	// Todo!
		{
			if ($ACTION == "GET") output ("ERROR_NONE");
		}
		else if ($NEXT_PARAM_NAME == "RSIPAvailable")	// Not Support yet.
		{
			if ($ACTION == "GET") output ("0");
		}
		else if ($NEXT_PARAM_NAME == "NATEnabled")	// In Current design, it is always on.
		{
			if ($ACTION == "GET") output ("1");
		}
		else if ($NEXT_PARAM_NAME == "DNSEnabled")	// In Current design, it is always on.
		{
			if ($ACTION == "GET") output ("1");
		}
		else if ($NEXT_PARAM_NAME == "DNSOverrideAllowed")
		{
			if ($ACTION == "GET")
			{
				$dns_cnt = query($path_wan_inet."/ipv4/dns/count");
				if ($dns_cnt > 0 ) { output ("1"); }
				else               { output ("0"); }
			}
			else if ($ACTION == "GET_PATH") { output ($path_wan_inet."/ipv4/dns/count"); }
			else if ($ACTION == "SET")	// We determine this value by chech if there is a dns server is set.
			{
			}
		}
		else if ($NEXT_PARAM_NAME == "DNSServers")
		{
			$inf_r_p = XNODE_getpathbytarget("/runtime", "inf", "uid", $WAN_UID, "0");
			if ($ACTION == "GET")
			{
				$dns_1 = query($inf_r_p."/inet/ipv4/dns:1");
				$dns_2 = query($inf_r_p."/inet/ipv4/dns:2");
				
				if ($dns_1 == "") { output (""); }	// no dns server.
				else 
				{
					if ($dns_2 == "") { output ($dns_1); }	// we have only 1 dns server.
					else              { output ($dns_1.",".$dns_2); }	// we have 2 dns servers.
				}
			}
			else if ($ACTION == "SET")
			{
				del ($path_wan_inet."/ipv4/dns");	// destory first.
				
				$dns_1 = cut($SET_VALUE, 0, ",");
				$dns_2 = cut($SET_VALUE, 1, ",");
				if ($dns_1 == "") 	// no dns server.
				{	set($path_wan_inet."/ipv4/dns/count", "0"); }
				else
				{
					set($path_wan_inet."/ipv4/dns/entry:1", $dns_1);
					if ($dns_2 == "") // 1 dns server.
					{
						set($path_wan_inet."/ipv4/dns/count", "1");
					}
					else	// 2 dns servers.
					{
						set($path_wan_inet."/ipv4/dns/count", "2");
						set($path_wan_inet."/ipv4/dns/entry:2", $dns_2);
					}
				}
			}
			// else if ($ACTION == "GET_PATH")	{	} nothing need to do.
		}
		else if ($NEXT_PARAM_NAME == "MACAddress")
		{
			$phyinf = query($inf_path."/phyinf");
			if ($ACTION == "GET" || $ACTION == "GET_PATH")
			{
				$phyinf_r_path = XNODE_getpathbytarget("/runtime", "phyinf", "uid", $phyinf, "0");
				exec_by_type($ACTION, $phyinf_r_path."/macaddr", $SET_VALUE); 
			}
			else if ($ACTION == "SET")
			{
				$phyinf_path = XNODE_getpathbytarget("", "phyinf", "uid", $phyinf, "0");
				set ($phyinf_path."/macaddr", $SET_VALUE);
			}
		}
		else if ($NEXT_PARAM_NAME == "ConnectionTrigger")	// for IPoE, we support always on only.
		{
			if ($ACTION == "GET")
			{
				output ("AlwaysOn");
			}
		}
		else if ($NEXT_PARAM_NAME == "RouteProtocolRx")	// Not supported by our device.
		{
			if ($ACTION == "GET") { output ("Off"); }
		}
		else if($NEXT_PARAM_NAME == "PortMappingNumberOfEntries"){
			$NODE_PATH = $BASE_ENTRY."/PortMapping/".$TR069_COUNT_STRING;
			
			if($ACTION == "GET" || $ACTION == "GET_PATH"){
				exec_by_type($ACTION, $NODE_PATH, $SET_VALUE);
			}
		}
		else if ($NEXT_PARAM_NAME == "EthernetBytesSent")
		{
			$phyinf_p = getPhyinfPath($WAN_UID);
			exec_by_type($ACTION, $phyinf_p."/stats/tx/bytes", $SET_VALUE);
		}
		else if ($NEXT_PARAM_NAME == "EthernetBytesReceived")
		{
			$phyinf_p = getPhyinfPath($WAN_UID);
			exec_by_type($ACTION, $phyinf_p."/stats/rx/bytes", $SET_VALUE);
		}
		else if ($NEXT_PARAM_NAME == "EthernetPacketsSent")
		{
			$phyinf_p = getPhyinfPath($WAN_UID);
			exec_by_type($ACTION, $phyinf_p."/stats/tx/packets", $SET_VALUE);
		}
		else if ($NEXT_PARAM_NAME == "EthernetPacketsReceived")
		{
			$phyinf_p = getPhyinfPath($WAN_UID);
			exec_by_type($ACTION, $phyinf_p."/stats/rx/packets", $SET_VALUE);
		}
		else
		{ TRACE_error("Not implemented yet. $PARAM_NAME=".$PARAM_NAME); }
		
		if ($ACTION == "SET")
		{
			fwrite("w", $EXEC_SHELL_FILE, "#!/bin/sh \n"); 
			fwrite("a", $EXEC_SHELL_FILE, "service INET.".$WAN_UID." restart \n"); 
		}
	}
}
?>