<?
include "/etc/scripts/tr069/phplib/io.php";
include "/etc/scripts/tr069/phplib/api.php";
include "/htdocs/webinc/feature.php";

if($NAME != "WANDevice") return;

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
				output(query($TRBASE."/wandevicenumberofentries"));
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
				foreach("/inf"){
					$WAN_UID = query("uid");
					if(strstr($WAN_UID,"WAN") != "" && query("name") == ""){
						$UID = query("phyinf");
						$success = TR069_AddObject(1,$NAME,$UID,"");
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
		if(strstr($PARAM_NAME, "WANDevice.") != ""){
			$NEXT_PARAM_NAME = cut($PARAM_NAME,1,".");
			if ($NEXT_PARAM_NAME == "WANConnectionNumberOfEntries"){
				$NODE_PATH = $BASE_ENTRY."/WANConnectionDevice/".$TR069_COUNT_STRING;
				exec_by_type($ACTION,$NODE_PATH,$SET_VALUE);
			}
		}
		else if(strstr($PARAM_NAME, "WANEthernetInterfaceConfig.") != ""){ //InternetGatewayDevice.WANDevice.{i}.WANEthernetInterfaceConfig. 
			$NEXT_PARAM_NAME = cut($PARAM_NAME,1,".");
			$UID = query($BASE_ENTRY."/UID");
			if ($UID != ""){
				$phyinf = $UID;
				$phyinf_p = XNODE_getpathbytarget("","phyinf", "uid", $phyinf, "0");
				$phyinf_runtime_p = XNODE_getpathbytarget("/runtime","phyinf", "uid", $phyinf, "0");

				if ($NEXT_PARAM_NAME == "Enable"){ 
					exec_by_type($ACTION, $phyinf_p."/active", $SET_VALUE);
				}
				else if ($NEXT_PARAM_NAME == "Status"){
					if ($ACTION == "GET_PATH"){
						output($phyinf_runtime_p."/linkstatus");
					}
					else if ($ACTION == "GET"){
						if (query($phyinf_p."/active") == "0"){ 
							output ("Disabled"); 
						}
						else{
							if (query($phyinf_runtime_p."/linkstatus") == "") output("NoLink");
							else											  output ("Up");
						}
					}
					else
						TRACE_error("Not support set in ".$NEXT_PARAM_NAME); 
				}
				else if ($NEXT_PARAM_NAME == "MACAddress"){ 
					exec_by_type($ACTION, $phyinf_runtime_p."/macaddr", $SET_VALUE);
				}
				else if ($NEXT_PARAM_NAME == "MaxBitRate" || $NEXT_PARAM_NAME == "DuplexMode"){
					$linkstatus = query($phyinf_p."/media/linktype");
					if ($ACTION == "GET_PATH"){ 
						output($phyinf_p."/media/linktype");
					}
					else if ($ACTION == "GET"){
						if ($linkstatus == "AUTO"){
							output("Auto"); 
						}
						else{
							if (strstr($linkstatus, "H") != "")  	  $duplex = "Half";
							else if (strstr($linkstatus, "F") != "")  $duplex = "Full";

							if ($NEXT_PARAM_NAME == "MaxBitRate"){
								if      (strstr($linkstatus, "1000") != "") output("1000");
								else if (strstr($linkstatus, "100") != "")  output("100");
								else if (strstr($linkstatus, "10") != "")   output("10");
							}
							else if ($NEXT_PARAM_NAME == "DuplexMode")
							{
								if      (strstr($linkstatus, "H") != "") output("Half");
								else if (strstr($linkstatus, "F") != "") output("Full");
							}
						}
					}
					else if ($ACTION == "SET"){
						if ($SET_VALUE == "Auto"){// HY: We could not set MaxBitRate and DuplexMode separately.
							set($phyinf_p."/media/linktype", "AUTO");
						}
						else{
							if ($linkstatus == "AUTO"){
								$maxbitrate = "1000"; 
								$duplex = "F";
							}
							else{
								if      (strstr($linkstatus, "1000") != "") $maxbitrate = "1000";
								else if (strstr($linkstatus, "100") != "")  $maxbitrate = "100";
								else if (strstr($linkstatus, "10") != "")   $maxbitrate = "10";

								if      (strstr($linkstatus, "H") != "")  $duplex = "H";
								else if (strstr($linkstatus, "F") != "")  $duplex = "F";
							}

							if ($NEXT_PARAM_NAME == "MaxBitRate"){
								set($phyinf_p."/media/linktype", $SET_VALUE.$duplex);
							}
							else if ($NEXT_PARAM_NAME == "DuplexMode"){
								if      ($SET_VALUE == "Half") set($phyinf_p."/media/linktype", $maxbitrate."H");
								else if ($SET_VALUE == "Full") set($phyinf_p."/media/linktype", $maxbitrate."F");
							}
						}
					}
				}
				else if ($NEXT_PARAM_NAME == "BytesSent")		exec_by_type($ACTION, $phyinf_runtime_p."/stats/tx/bytes", $SET_VALUE);
				else if ($NEXT_PARAM_NAME == "BytesReceived") 	exec_by_type($ACTION, $phyinf_runtime_p."/stats/rx/bytes", $SET_VALUE);
				else if ($NEXT_PARAM_NAME == "PacketsSent")		exec_by_type($ACTION, $phyinf_runtime_p."/stats/tx/packets", $SET_VALUE);
				else if ($NEXT_PARAM_NAME == "PacketsReceived")	exec_by_type($ACTION, $phyinf_runtime_p."/stats/rx/packets", $SET_VALUE);
				
				if ($ACTION == "SET")
				{
					fwrite("w", $EXEC_SHELL_FILE, "#!/bin/sh \n"); 
					fwrite("a", $EXEC_SHELL_FILE, "service PHYINF.".$phyinf." restart \n"); 
				}
			}
		}
		else if(strstr($PARAM_NAME, "WANCommonInterfaceConfig.") != ""){ //InternetGatewayDevice.WANDevice.{i}.WANCommonInterfaceConfig. 
			$NEXT_PARAM_NAME = cut($PARAM_NAME,1,".");
			$UID = query($BASE_ENTRY."/UID");
			if ($UID != ""){
				$phyinf = $UID;                                              
				$phyinf_p = XNODE_getpathbytarget("","phyinf", "uid", $phyinf, "0");                 
				$phyinf_runtime_p = XNODE_getpathbytarget("/runtime","phyinf", "uid", $phyinf, "0"); 
				if($NEXT_PARAM_NAME == "EnabledForInternet"){
					//No Support
				}
				else if($NEXT_PARAM_NAME == "WANAccessType"){output("Ethernet");}
				else if($NEXT_PARAM_NAME == "Layer1UpstreamMaxBitRate" || $NEXT_PARAM_NAME == "Layer1DownstreamMaxBitRate"){
					if($FEATURE_WAN1000FTYPE == 1) output("1073741824");
					else						   output("1048576");
				}
				else if($NEXT_PARAM_NAME == "PhysicalLinkStatus"){
					$NODE_PATH = $phyinf_runtime_p."/linkstatus";
					if($ACTION == "GET"){
						if(query($phyinf_p."/active") != 1) output("Unavailable");
						else{
							if(query($NODE_PATH) == 1) output("Up");
							else					   output("Down");
						}
					}
					else
						exec_by_type($ACTION,$NODE_PATH,$SET_VALUE);
				}
				else if ($NEXT_PARAM_NAME == "TotalBytesSent") 		 exec_by_type($ACTION, $phyinf_runtime_p."/stats/tx/bytes", $SET_VALUE);
				else if ($NEXT_PARAM_NAME == "TotalBytesReceived") 	 exec_by_type($ACTION, $phyinf_runtime_p."/stats/rx/bytes", $SET_VALUE);
				else if ($NEXT_PARAM_NAME == "TotalPacketsSent") 	 exec_by_type($ACTION, $phyinf_runtime_p."/stats/tx/packets", $SET_VALUE);
				else if ($NEXT_PARAM_NAME == "TotalPacketsReceived") exec_by_type($ACTION, $phyinf_runtime_p."/stats/rx/packets", $SET_VALUE);
			}
		}
		else
			TRACE_error("Not implemented yet. $PARAM_NAME=".$PARAM_NAME);
	}
}
?>