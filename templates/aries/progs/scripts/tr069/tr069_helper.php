<?
include "/etc/scripts/tr069/phplib/io.php";
include "/etc/scripts/tr069/phplib/env.php";
include "/etc/scripts/tr069/phplib/dualwan.php";
include "/htdocs/webinc/config.php";

if(query("/tr069/external/enable_dualwan")=="1")
	$UID = $RUNTIME_UID;
else
	if($UID == "") $UID = $WAN1;


if($BASIC_OP_FLAG == 1){ //For Basic Operations
	if($ACTION == "GET") {
		if($NAME == "phyinf"){
			foreach("/runtime/inf") {
				$uid = query("uid");
				if ($uid == $UID) {
					$phyinf = query("phyinf");
					break;
				}
			}
			foreach("/runtime/phyinf") {
				$uid = query("uid");
				if ($uid == $phyinf) {
					output(query("name"));
					break;
				}
			}
		}
		else if($NAME == "wan_mode"){
			$path_inf_wan = XNODE_getpathbytarget("", "inf", "uid", $UID, 0);
			$wan_inet = query($path_inf_wan."/inet");
			$path_wan_inet = XNODE_getpathbytarget("/inet", "entry", "uid", $wan_inet, 0);
			anchor($path_wan_inet);
	
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
		else if($NAME == "lanmac" || $NAME == "wanmac" || $NAME == "wlanmac" ||  $NAME == "wlan5mac"){output(query("/runtime/devdata/".$NAME));}
		else
			output(query($NAME));
	}
	else if($ACTION == "SET"){set($NAME,$SET_VALUE);}
	else if($ACTION == "DEL"){del($NAME);}
	else if($ACTION == "GET_ATTR"){output(getattr($NAME,$ATTR_NAME));}
	else if($ACTION == "SET_ATTR"){setattr($NAME,$ATTR_NAME,$ATTR_VALUE);}
}
else{
//Single-Object
	if($NAME == "DeviceSummary"){
		$NODE_PATH = $TRBASE."/".tolower($NAME);
		exec_by_type($ACTION,$NODE_PATH,$SET_VALUE);
	}
	else if($NAME == "LanDeviceNumberofEntries"){
		$NODE_PATH = $TR069_MULTI_BASE."/LANDevice/".$TR069_COUNT_STRING;
		exec_by_type($ACTION,$NODE_PATH,$SET_VALUE);
	}
	else if($NAME == "WanDeviceNumberofEntries"){
		$NODE_PATH = $TR069_MULTI_BASE."/WANDevice/".$TR069_COUNT_STRING;
		exec_by_type($ACTION,$NODE_PATH,$SET_VALUE);
	}
	else{
		if(strstr($NAME,"PerformanceDiagnostic.") != ""){
			//InternetGatewayDevice.Capabilities.PerformanceDiagnostic.
			$PARAM_NAME = cut($NAME,1,".");
			$NODE_PATH = $TRBASE."/performancediagnostic/".tolower($PARAM_NAME);
			exec_by_type($ACTION,$NODE_PATH,$SET_VALUE);
		}
		else if(strstr($NAME,"DeviceInfo.") != ""){
			//InternetGatewayDevice.DeviceInfo.
			$PARAM_NAME = cut($NAME,1,".");
			if($PARAM_NAME == "Uptime")
				$NODE_PATH = "/runtime/device/uptime";
			else
				$NODE_PATH = $TRBASE."/deviceinfo/".tolower($PARAM_NAME);
			exec_by_type($ACTION,$NODE_PATH,$SET_VALUE);
		}
		else if(strstr($NAME,"ManagementServer.") != ""){
			//InternetGatewayDevice.ManagementServer.
			$PARAM_NAME = cut($NAME,1,".");
			if($PARAM_NAME == "ConnectionRequestURL"){
				$NODE_PATH = $TRBASE."/managementserver/".tolower($PARAM_NAME);
				if(query($NODE_PATH) == ""){
					if($ACTION == "GET"){
						foreach("/runtime/inf") {
							$uid = query("uid");
							if ($uid == $UID) {
								$type = query("inet/addrtype");
								if ($type == "ipv4") {$ipaddr = query("inet/ipv4/ipaddr");} 
								else {
									if ($type == "ppp4") {$ipaddr = query("inet/ppp4/local");} 
									else 				 {$ipaddr = query("inet/ipv6/ipaddr");}
								}
								break;
							}
						}
						$mac = PHYINF_getphymac("LAN-1");
						$rndstr = cut($mac, 0, ":").cut($mac, 1, ":").cut($mac, 2, ":").cut($mac, 3, ":").cut($mac, 4, ":").cut($mac, 5, ":");
						$NODE_PATH = $RUNTIME_TRBASE."/managementserver/".tolower($PARAM_NAME);
						$URL = "http://".$ipaddr.":".query($TRBASE."/misc/connectionrequestport")."/".tolower($rndstr);
						set($NODE_PATH,$URL);
					}
				}
			}
			else if($PARAM_NAME == "UDPConnectionRequestAddress" || $PARAM_NAME == "NATDetected"){
				$NODE_PATH = $RUNTIME_TRBASE."/managementserver/stun/".tolower($PARAM_NAME);
			}
			else if(strstr($PARAM_NAME,"STUN") != ""){
				$NODE_PATH = $TRBASE."/managementserver/stun/".tolower($PARAM_NAME);
			}
			else if($PARAM_NAME == "ManageableDeviceNumberOfEntries"){
				$NODE_PATH = $TR069_MULTI_BASE."/ManageableDevice/".$TR069_COUNT_STRING;
			}
			else
				$NODE_PATH = $TRBASE."/managementserver/".tolower($PARAM_NAME);
			exec_by_type($ACTION,$NODE_PATH,$SET_VALUE);
		}
		else if(strstr($NAME,"Layer3Forwarding.") != ""){
			$PARAM_NAME = cut($NAME,1,".");
			if($PARAM_NAME == "DefaultConnectionService"){
				if ($ACTION == "GET"){
					$inf_p = XNODE_getpathbytarget("/runtime", "inf", "uid", $UID, "0");
					$addrtype = get("", $inf_p."/inet/addrtype");
					if ($addrtype == "ipv4")	// currently, only IPoE or PPPoE.
					{ output ("InternetGatewayDevice.WANDevice.1.WANConnectionDevice.1.WANIPConnection.1."); }
					else if ($addrtype == "ppp4" || $addrtype == "ppp10")
					{ output ("InternetGatewayDevice.WANDevice.1.WANConnectionDevice.1.WANPPPConnection.1."); }
				}
			}
			else if($PARAM_NAME == "ForwardNumberOfEntries"){
				exec_by_type($ACTION, $TRBASE."/dynamic/multiobject/Forwarding/count", $SET_VALUE);
			}
		}
		else if(strstr($NAME,"LANConfigSecurity.") != ""){
			$PARAM_NAME = cut($NAME,1,".");
			if($PARAM_NAME == "ConfigPassword"){
				if ($ACTION == "SET"){
					$user = "Admin";
					$user_p = XNODE_getpathbytarget("/device/account", "entry", "name", $user, "0");
					set($user_p."/password", $SET_VALUE);
				}
				else
				{ output (""); }
			}
		}
		else if(strstr($NAME,"UDPEchoConfig.") != ""){
			$PARAM_NAME = cut($NAME,1,".");
			$NODE_PATH = $TRBASE."/tr143/udpechoconfig/".tolower($PARAM_NAME);
			exec_by_type($ACTION,$NODE_PATH,$SET_VALUE);
		}
		else if(strstr($NAME,"IPPingDiagnostics.") != ""){
			$PARAM_NAME = cut($NAME,1,".");
			$NODE_PATH = $RUNTIME_TRBASE."/diagnostics/ping/".tolower($PARAM_NAME);
			exec_by_type($ACTION,$NODE_PATH,$SET_VALUE);
		}
		else if(strstr($NAME,"DownloadDiagnostics.") != ""){
			$PARAM_NAME = cut($NAME,1,".");
			$NODE_PATH = $RUNTIME_TRBASE."/diagnostics/download/".tolower($PARAM_NAME);
			exec_by_type($ACTION,$NODE_PATH,$SET_VALUE);
		}
		else if(strstr($NAME,"UploadDiagnostics.") != ""){
			$PARAM_NAME = cut($NAME,1,".");
			$NODE_PATH = $RUNTIME_TRBASE."/diagnostics/upload/".tolower($PARAM_NAME);
			exec_by_type($ACTION,$NODE_PATH,$SET_VALUE);
		}
		else
			TRACE_error("tr069_helper: Error! Unknown type name = ".$NAME);
	}
}
?>