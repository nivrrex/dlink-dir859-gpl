<?
include "/etc/scripts/tr069/phplib/io.php";
include "/etc/scripts/tr069/phplib/api.php";

if($NAME != "WANPPPConnection") return;

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
					if(query("addrtype") == "ppp4") $cnt++;
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
					if(query("addrtype") == "ppp4"){
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
		$inf_p = XNODE_getpathbytarget("", "inf", "uid", $WAN_UID,"0");
		$inf_runtime_p = XNODE_getpathbytarget("/runtime","inf", "uid", $WAN_UID, "0");
		
		$INET_UID = query($BASE_ENTRY."/UID");
		$inet_p = XNODE_getpathbytarget("/inet", "entry", "uid", $INET_UID, 0);
			
		$phyinf = query($inf_p."/phyinf");
		$phyinf_p = XNODE_getpathbytarget("","phyinf", "uid", $phyinf, "0");
		$phyinf_runtime_p = XNODE_getpathbytarget("/runtime","phyinf", "uid", $phyinf, "0");
			
		if(strstr($PARAM_NAME, "Stats.") != ""){
			$NEXT_PARAM_NAME = cut($PARAM_NAME,1,".");
			if ($NEXT_PARAM_NAME == "EthernetBytesSent"){
				exec_by_type($ACTION, $phyinf_runtime_p."/stats/tx/bytes", $SET_VALUE);
			}
			else if ($NEXT_PARAM_NAME == "EthernetBytesReceived"){
				exec_by_type($ACTION, $phyinf_runtime_p."/stats/rx/bytes", $SET_VALUE);
			}
			else if ($NEXT_PARAM_NAME == "EthernetPacketsSent"){
				exec_by_type($ACTION, $phyinf_runtime_p."/stats/tx/packets", $SET_VALUE);
			}
			else if ($NEXT_PARAM_NAME == "EthernetPacketsReceived"){
				exec_by_type($ACTION, $phyinf_runtime_p."/stats/rx/packets", $SET_VALUE);
			}
		}
		else if(strstr($PARAM_NAME, "WANPPPConnection.") != ""){
			$NEXT_PARAM_NAME = cut($PARAM_NAME,1,".");
			
			if($NEXT_PARAM_NAME == "Enable"){
				 exec_by_type($ACTION, $inf_p."/active", $SET_VALUE); 
			}
			else if($NEXT_PARAM_NAME == "ConnectionStatus"){
				if($ACTION == "GET"){
					if(query($inf_p."/active") == "0"){
						output("Unconfigured");
					}
					else{
						if(query($phyinf_runtime_p."/linkstatus")!=""){
							$wancable_status = 1;
						}
						else{
							$wancable_status = 0;
						}
						
						if(query($inf_runtime_p."/inet/ppp4/valid") == "1" && $wancable_status == 1){
							$wan_network_status = 1;
						}
						else{
							$wan_network_status = 0;
						}
						
						if(query($inf_runtime_p."/pppd/status") == "connected"){
							if($wan_network_status == 1){
								output("Connected");
							}
							else{
								output("Disconnected");
							}
						}
						else if(query($inf_runtime_p."/pppd/status") == ""){
							output("Disconnected");
						}
						else{
							output("Connecting");
						}
					}
				}
			}
			else if($NEXT_PARAM_NAME == "PossibleConnectionTypes" || $NEXT_PARAM_NAME == "ConnectionType" || $NEXT_PARAM_NAME == "TransportType"){
				if($ACTION == "GET"){
					$mode = query($inet_p."/addrtype");
					$over = query($inet_p."/ppp4/over");
					
					if($NEXT_PARAM_NAME == "PossibleConnectionTypes" || $NEXT_PARAM_NAME == "ConnectionType"){
						$relay = "_Relay";
					}
					else if($NEXT_PARAM_NAME == "TransportType"){
						$relay = "";
					}
					
					if($mode == "ppp10"){
						output("PPPoE".$relay);
					}
					else if($mode == "ppp4" && $over == "eth"){
						output("PPPoE".$relay);
					}
					else if($mode == "ppp4" && $over == "pptp"){
						output("PPTP".$relay);
					}
					else if($mode == "ppp4" && $over == "l2tp"){
						output("L2TP".$relay);
					}
				}
			}
			else if($NEXT_PARAM_NAME == "Name"){
				$NODE_PATH = $BASE_ENTRY."/name";
				exec_by_type($ACTION, $NODE_PATH, $SET_VALUE);
			}
			else if($NEXT_PARAM_NAME == "Uptime"){
				if($ACTION == "GET"){
					$wan_uptime = query($inf_runtime_p."/inet/uptime");
					$system_uptime = query("/runtime/device/uptime");
					$uptime = $system_uptime - $wan_uptime;
					output($uptime);
				}
			}
			else if($NEXT_PARAM_NAME == "LastConnectionError"){
				$ppp_process = $inf_runtime_p."/pppd/process";
				if ($ACTION == "GET"){
					if($ppp_process == "authFailed"){
						output("ERROR_AUTHENTICATION_FAILURE");
					}
					else {
						output("ERROR_NONE");
					}
				}
			}
			else if($NEXT_PARAM_NAME == "RSIPAvailable"){ //Not support
				if ($ACTION == "GET"){
					output("0");
				}
			}
			else if($NEXT_PARAM_NAME == "NATEnabled"){ //Not support
				if ($ACTION == "GET"){
					output("0");
				}
			}
			else if($NEXT_PARAM_NAME == "Username"){
				exec_by_type($ACTION, $inet_p."/ppp4/username", $SET_VALUE);
			}
			else if($NEXT_PARAM_NAME == "Password"){
				exec_by_type($ACTION, $inet_p."/ppp4/password", $SET_VALUE);
			}
			else if($NEXT_PARAM_NAME == "ExternalIPAddress"){
				exec_by_type($ACTION, $inf_runtime_p."/inet/ppp4/local", $SET_VALUE);
			}
			else if($NEXT_PARAM_NAME == "DNSEnabled"){ //In current design, it is always on.
				if($ACTION == "GET"){
					output("1");
				}
			}
			else if($NEXT_PARAM_NAME == "DNSOverrideAllowed"){
				$NODE_PATH = $inet_p."/ipv4/dns/count";
				if ($ACTION == "GET"){
					$dns_cnt = query($NODE_PATH);
					if ($dns_cnt > 0 ) { output("1"); }
					else               { output("0"); }
				}
				else if($ACTION == "GET_PATH"){
					output ($NODE_PATH);
				}
				else if($ACTION == "SET"){
				}
			}
			else if($NEXT_PARAM_NAME == "DNSServers"){
				if ($ACTION == "GET"){
					$NODE_PATH = $inf_runtime_p."/inet/ppp4/dns";
					
					$dns_1 = query($NODE_PATH.":1");
					$dns_2 = query($NODE_PATH.":2");
					
					if($dns_1 == ""){ output (""); }
					else{
						if ($dns_2 == "") { output ($dns_1); }
						else              { output ($dns_1.",".$dns_2); }
					}
				}
				else if ($ACTION == "SET"){
					$NODE_PATH = $inet_p."/ipv4/dns";
					del($NODE_PATH);
					
					$dns_1 = cut($SET_VALUE, 0, ",");
					$dns_2 = cut($SET_VALUE, 1, ",");
					if ($dns_1 == ""){
						set($NODE_PATH."/count", "0");
					}
					else{
						set($NODE_PATH."/entry:1", $dns_1);
						if ($dns_2 == ""){
							set($NODE_PATH."/count", "1");
						}
						else{
							set($NODE_PATH."/count", "2");
							set($NODE_PATH."/entry:2", $dns_2);
						}
					}
				}
			}
			else if($NEXT_PARAM_NAME == "MACAddress"){
				if($ACTION == "GET" || $ACTION == "GET_PATH"){
					$NODE_PATH = $phyinf_runtime_p."/macaddr";
				}
				else{
					$NODE_PATH = $phyinf_p."/macaddr";
				}
				exec_by_type($ACTION, $NODE_PATH, $SET_VALUE);
			}
			else if($NEXT_PARAM_NAME == "PPPoEACName"){ //Not support
				if($ACTION == "GET"){
					output("0");
				}
			}
			else if($NEXT_PARAM_NAME == "PPPoEServiceName"){
				$mode = query($inet_p."/addrtype");
				$over = query($inet_p."/ppp4/over");
				
				if($over == "eth"){
					$NODE_PATH = $inet_p."/ppp4/pppoe/server";
				}
				else if($over == "pptp"){
					$NODE_PATH = $inet_p."/ppp4/pptp/server";
				}
				else if($over == "l2tp"){
					$NODE_PATH = $inet_p."/ppp4/l2tp/server";
				}
				
				exec_by_type($ACTION, $NODE_PATH, $SET_VALUE);
			}
			else if($NEXT_PARAM_NAME == "ConnectionTrigger"){
				$NODE_PATH = $inet_p."/ppp4/dialup/mode";
				if($ACTION == "GET"){
					$dial_mode = query($NODE_PATH);
					if($dial_mode=="ondemand") { output("OnDemand"); }
					else if($dial_mode=="auto") { output("AlwaysOn"); }
					else if($dial_mode=="manual") { output("Manual"); }
				}
				else if($ACTION == "SET"){
					if($SET_VALUE == "OnDemand") {$mode = "ondemand";}
					else if($SET_VALUE == "AlwaysOn") {$mode = "auto";}
					else if($SET_VALUE == "Manual") {$mode = "manual";}
					set($NODE_PATH, $mode);
				}
				else if($ACTION == "GET_PATH"){ output($NODE_PATH); }
			}
			else if($NEXT_PARAM_NAME == "RouteProtocolRx"){ //Not support
				if($ACTION == "GET"){
					output("Off");
				}
			}
			else if($NEXT_PARAM_NAME == "PortMappingNumberOfEntries"){
				$NODE_PATH = $BASE_ENTRY."/PortMapping/".$TR069_COUNT_STRING;
				
				if($ACTION == "GET" || $ACTION == "GET_PATH"){
					exec_by_type($ACTION, $NODE_PATH, $SET_VALUE);
				}
			}
			else{
				TRACE_error("Not implemented yet. $PARAM_NAME=".$PARAM_NAME);
			}
			if ($ACTION == "SET")
			{
				fwrite("w", $EXEC_SHELL_FILE, "#!/bin/sh \n"); 
				fwrite("a", $EXEC_SHELL_FILE, "service INET.".$WAN_UID." restart \n"); 
			}
		}
	}
}
?>
