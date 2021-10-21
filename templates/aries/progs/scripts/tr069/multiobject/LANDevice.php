<?
include "/etc/scripts/tr069/phplib/io.php";
include "/etc/scripts/tr069/phplib/api.php";

if($NAME != "LANDevice") return;

$BASE = $TR069_MULTI_BASE."/".$NAME;
$BASE_ENTRY = $BASE."/entry:".$INDEX0;

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
				$LAN_UID = "LAN-".$SET_VALUE;
				TR069_AddObject(1,$NAME,$LAN_UID,"");
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
		
		if(strstr($PARAM_NAME, "LANDevice.") != ""){
			if ($NEXT_PARAM_NAME == "LANEthernetInterfaceNumberOfEntries"){
				if($ACTION == "GET" || $ACTION == "GET_PATH"){
					$NODE_PATH = $BASE_ENTRY."/LANEthernetInterfaceConfig/".$TR069_COUNT_STRING;
					exec_by_type($ACTION,$NODE_PATH,$SET_VALUE);
				}
			}
			else if ($NEXT_PARAM_NAME == "LANUSBInterfaceNumberOfEntries"){
				if($ACTION == "GET" || $ACTION == "GET_PATH"){
					$NODE_PATH = $BASE_ENTRY."/LANUSBInterfaceConfig/".$TR069_COUNT_STRING;
					exec_by_type($ACTION,$NODE_PATH,$SET_VALUE);
				}
			}
			else if ($NEXT_PARAM_NAME == "LANWLANConfigurationNumberOfEntries"){
				if($ACTION == "GET" || $ACTION == "GET_PATH"){
					$NODE_PATH = $BASE_ENTRY."/WLANConfiguration/".$TR069_COUNT_STRING;
					exec_by_type($ACTION,$NODE_PATH,$SET_VALUE);
				}
			}
		}
		else if(strstr($PARAM_NAME, "LANHostConfigManagement.") != ""){
			$LAN_UID = query($BASE_ENTRY."/UID");
			
			$inf_p = XNODE_getpathbytarget("", "inf", "uid", $LAN_UID,"0");
			$inf_runtime_p = XNODE_getpathbytarget("/runtime","inf", "uid", $LAN_UID, 0);
			
			$inet = query($inf_p."/inet");
			$inet_p = XNODE_getpathbytarget("/inet", "entry", "uid", $inet, 0);

			$dhcps4 = query($inf_p."/dhcps4");
			$dhcps4_p = XNODE_getpathbytarget("dhcps4", "entry", "uid", $dhcps4, 0);
					
			$lanip = query($inet_p."/ipv4/ipaddr");
			$mask = query($inet_p."/ipv4/mask");
			
			if ($NEXT_PARAM_NAME == "DHCPServerConfigurable"){ /*In current design, it is always enabled.*/
				if($ACTION == "GET"){
					output("1");
				}
			}
			else if ($NEXT_PARAM_NAME == "DHCPServerEnable"){
				$NODE_PATH = $inf_p."/dhcps4";
				if($ACTION == "GET"){
					$dhcps_en = query($NODE_PATH);
					if($dhcps_en != ""){ output("1"); }
					else{ output("0"); }
				}
				else if($ACTION == "SET"){
					if($SET_VALUE == "0"){ set($NODE_PATH, ""); }
					else if($SET_VALUE == "1"){
						if($LAN_UID == "LAN-1"){ set($NODE_PATH, "DHCPS4-1"); }
						else if($LAN_UID == "LAN-2"){ set($NODE_PATH, "DHCPS4-2"); }
					}
				}
				else if($ACTION == "GET_PATH"){ output($NODE_PATH); }
			}
			else if ($NEXT_PARAM_NAME == "DHCPRelay"){
				$NODE_PATH = $inf_p."/dns4";
				if($ACTION == "GET"){
					$dhcp_relay = query($NODE_PATH);
					if($dhcp_relay != ""){ output("1"); }
					else{ output("0"); }
				}
				else if($ACTION == "SET"){
					if($SET_VALUE == "0"){ set($NODE_PATH, ""); }
					else if($SET_VALUE == "1"){ set($NODE_PATH, "DNS4-1"); }
				}
				else if($ACTION == "GET_PATH"){ output($NODE_PATH); }
			}
			else if ($NEXT_PARAM_NAME == "MinAddress" || $NEXT_PARAM_NAME == "MaxAddress"){
				if($NEXT_PARAM_NAME == "MinAddress") { $type = "start"; }
				else if($NEXT_PARAM_NAME == "MaxAddress") { $type = "end"; }
				
				$NODE_PATH = $dhcps4_p."/".$type;
				if($ACTION == "GET"){
					$id = query($NODE_PATH);
					$addr = ipv4ip($lanip, $mask, $id);
					output($addr);
				}
				else if($ACTION == "SET"){
					$ipv4addr = $SET_VALUE;
					$hostid = ipv4hostid($ipv4addr, $mask);
					set($NODE_PATH, $hostid);
				}
				else if($ACTION == "GET_PATH"){ output($NODE_PATH); }
			}
			else if ($NEXT_PARAM_NAME == "ReservedAddresses"){
				$NODE_PATH = $dhcps4_p."/staticleases";
				if($ACTION == "GET"){
					$reserved_list = "";
					$reserved_list_cnt = query($NODE_PATH."/count");
					if($reserved_list_cnt == 0){
						output("");
					}
					else{
						foreach($NODE_PATH."/entry"){
							$hosid = query("hostid");
							$reserved_addr = ipv4ip($lanip, $mask, $hosid);
							if($reserved_list_cnt != $InDeX){
								$reserved_addr = $reserved_addr.",";
							}
							$reserved_list = $reserved_list.$reserved_addr;
						}
						output($reserved_list);
					}
				}
				else if($ACTION == "SET"){
					$reserved_list_cnt = scut_count($SET_VALUE, ",");
					$reserved_list_cnt = $reserved_list_cnt + 1;
					set($NODE_PATH."/count", $reserved_list_cnt);
					$list_index = 0;
					while($list_index < $reserved_list_cnt){
						$addr = cut($SET_VALUE, $list_index, ",");
						$hostid = ipv4hostid($addr, $mask);
						$list_index++;
						set($NODE_PATH."/entry:".$list_index."/hostid", $hostid);
					}
				}
			}
			else if ($NEXT_PARAM_NAME == "SubnetMask"){
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
			else if ($NEXT_PARAM_NAME == "DNSServers"){
				$NODE_PATH = $inet_p."/ipv4/dns";
				if ($ACTION == "GET"){
					$dns_cnt = query($NODE_PATH."/count");
					$dns_1 = query($NODE_PATH."/entry:1");
					$dns_2 = query($NODE_PATH."/entry:2");
					
					if($dns_cnt == 0){
						output ("");
					}
					else{
						if ($dns_2 == "") { output($dns_1); }
						else              { output($dns_1.",".$dns_2); }
					}
				}
				else if ($ACTION == "SET"){
					del ($NODE_PATH);
					$dns_1 = cut($SET_VALUE, 0, ",");
					$dns_2 = cut($SET_VALUE, 1, ",");
					if ($dns_1 == ""){
						set($NODE_PATH."/count", 0);
					}
					else{
						set($NODE_PATH."/entry:1", $dns_1);
						if ($dns_2 == ""){
							set($NODE_PATH."/count", 1);
						}
						else{
							set($NODE_PATH."/count", "2");
							set($NODE_PATH."/entry:2", $dns_2);
						}
					}
				}
			}
			else if ($NEXT_PARAM_NAME == "DomainName"){
				$NODE_PATH = $dhcps4_p."/domain";
				exec_by_type($ACTION, $NODE_PATH, $SET_VALUE);
			}
			else if ($NEXT_PARAM_NAME == "IPRouters"){
				$NODE_PATH = $inet_p."/ipv4/ipaddr";
				exec_by_type($ACTION, $NODE_PATH, $SET_VALUE);
			}
			else if ($NEXT_PARAM_NAME == "DHCPLeaseTime"){
				$NODE_PATH = $dhcps4_p."/leasetime";
				exec_by_type($ACTION, $NODE_PATH, $SET_VALUE);
			}
			else if ($NEXT_PARAM_NAME == "IPInterfaceNumberOfEntries"){
				if($ACTION == "GET" || $ACTION == "GET_PATH"){
					$NODE_PATH = $BASE_ENTRY."/LANHostConfigManagement/IPInterface/".$TR069_COUNT_STRING;
					exec_by_type($ACTION, $NODE_PATH, $SET_VALUE);
				}
			}
			
			if ($ACTION == "SET")
			{
				fwrite("w", $EXEC_SHELL_FILE, "#!/bin/sh \n"); 
				fwrite("a", $EXEC_SHELL_FILE, "service INET.".$LAN_UID." restart \n"); 
			}
		}
		else if(strstr($PARAM_NAME, "Hosts.") != ""){
			if ($NEXT_PARAM_NAME == "HostNumberOfEntries"){
				$NODE_PATH = $BASE_ENTRY."/Hosts/Host/".$TR069_COUNT_STRING;
				exec_by_type($ACTION, $NODE_PATH, $SET_VALUE);
			}
		}
	}
}
?>