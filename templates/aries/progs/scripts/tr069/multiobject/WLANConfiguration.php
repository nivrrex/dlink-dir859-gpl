<?
include "/etc/scripts/tr069/phplib/io.php";
include "/etc/scripts/tr069/phplib/api.php";
include "/htdocs/webinc/config.php";
include "/htdocs/webinc/feature.php";

/* let guestzone_mac = host_mac + 1*/
function get_guestzone_mac($host_mac)
{
	$index = 5;
	$guestzone_mac = "";
	$carry = 0;

	//loop from low byte to high byte
	//ex: 00:01:02:03:04:05
	//05 -> 04 -> 03 -> 02 -> 01 -> 00
	while($index >= 0)
	{
		$field = cut($host_mac , $index , ":");

		//check mac format
		if($field == "") { return ""; }

		//to value
		$value = strtoul($field , 16);
		if($value == "") { return ""; }

		if($index == 5) { $value = $value + 1; }

		//need carry?
		$value = $value + $carry;
		if($value > 255)
		{
			$carry = 1;
			$value = $value % 256;
		}
		else { $carry = 0; }

		//from dec to hex
		$hex_value = dec2strf("%02X" , $value);

		if($guestzone_mac == "") { $guestzone_mac = $hex_value; }
		else { $guestzone_mac = $hex_value.":".$guestzone_mac; }

		$index = $index - 1;
	}
	return $guestzone_mac;
}

function get_wepkey_index($path)
{
	$wep_key_index = "";
	foreach($path."/key")
	{
		if(query($path."/key:".$InDeX) != "")
		{
			$wep_key_index = $InDeX;
			break;
		}
	}
	return $wep_key_index;
}

if($NAME != "WLANConfiguration") return;

$TOP_PATH = $TR069_MULTI_BASE."/LANDevice/entry:".$INDEX0;
$BASE = $TOP_PATH."/".$NAME;
$BASE_ENTRY = $BASE."/entry:".$INDEX1;

if($FEATURE_DUAL_BAND == 1)
{
	$support_dualband = true;
}
else
{
	$support_dualband = false;
}

if($FEATURE_NOGUESTZONE == 1)
{
	$support_guestzone = false;
}
else
{
	$support_guestzone = true;
}

if($ACTION == "DEL")
{
	$P_UID = query($TOP_PATH."/UID");
	$UID = query($BASE_ENTRY."/UID");
	TR069_DeleteObject(1,$NAME,$UID,$P_UID);
}
else if($ACTION == "GET_INDEX")
{
	$entry_index = 0;
	$instance_num = $INSTANCE;
	foreach($BASE."/entry")
	{
		$tr069_index = getattr("",$TR069_INDEX_STRING);
		if($tr069_index == $instance_num)
		{
			$entry_index = $InDeX;
			break;
		}
	}
	output($entry_index);
}
else{
	if($PARAM_NAME == $TR069_COUNT_STRING)
	{
		$NODE_PATH = $BASE."/".$TR069_COUNT_STRING;
		
		if($ACTION == "GET")
		{
			if(query($NODE_PATH) == "")
			{
				set($NODE_PATH,0);
				output(query($TRBASE."/lanwlanconfigurationnumberofentries"));
			}
			else { output(query($NODE_PATH)); }
		}
		else if($ACTION == "SET") { set($NODE_PATH,$SET_VALUE); }
		else if($ACTION == "GET_PATH") { output($NODE_PATH); }
	}
	else if($PARAM_NAME == $TR069_SEQ_STRING)
	{
		$NODE_PATH = $BASE;
		if($ACTION == "GET") {	output(getattr($NODE_PATH, $TR069_SEQ_STRING)); }
		else if($ACTION == "SET") {	setattr($NODE_PATH, $TR069_SEQ_STRING, $SET_VALUE); }
	}
	else if($PARAM_NAME == $TR069_INDEX_STRING)
	{
		$NODE_PATH = $BASE_ENTRY;
		if($ACTION == "GET") {	output(getattr($NODE_PATH, $TR069_INDEX_STRING)); }
		else if($ACTION == "SET")
		{
			setattr($NODE_PATH,$TR069_INDEX_STRING,$SET_VALUE);
			if(query($BASE_ENTRY."/UID") == "") //Note: Avoid a wrong loop
			{ 
				$P_UID = query($TOP_PATH."/UID");
				if($SET_VALUE == 1) { $UID = $WLAN1; }
				
				if($support_dualband == true && $support_guestzone == true) /* WLAN1, WLAN2, $WLAN1_GZ, $WLAN2_GZ */
				{
					if($SET_VALUE == 2) { $UID = $WLAN2; }
					else if($SET_VALUE == 3) { $UID = $WLAN1_GZ; }
					else if($SET_VALUE == 4) { $UID = $WLAN2_GZ; }
				}
				else if($support_dualband == true && $support_guestzone == false) /* WLAN1, WLAN2 */
				{
					if($SET_VALUE == 2) { $UID = $WLAN2; }
				}
				else if($support_dualband == false && $support_guestzone == false) /* WLAN1, $WLAN1_GZ */
				{
					if($SET_VALUE == 2) { $UID = $WLAN1_GZ; }
				}
				TR069_AddObject(1, $NAME, $UID, $P_UID);
			}
		}
	}
	else if($PARAM_NAME == $TR069_PATH_STRING)
	{
		$NODE_PATH = $BASE_ENTRY;
		if($ACTION == "GET") { output(getattr($NODE_PATH,$TR069_PATH_STRING)); }
		else if($ACTION == "SET") { setattr($NODE_PATH,$TR069_PATH_STRING,$SET_VALUE); }
	}
	else
	{
		if(strstr($PARAM_NAME, "WLANConfiguration.") != "")
		{
			$NEXT_PARAM_NAME = cut($PARAM_NAME,1,".");
			$WLAN_UID = query($BASE_ENTRY."/UID");

			if($WLAN_UID == $WLAN1 || $WLAN_UID == $WLAN2)
			{
				$phyinf_wlan_host_p = XNODE_getpathbytarget("", "phyinf", "uid", $WLAN_UID, 0);
				$phyinf_wlan_p = $phyinf_wlan_host_p;
			}
			else if($WLAN_UID == $WLAN1_GZ)
			{
				$phyinf_wlan_host_p = XNODE_getpathbytarget("", "phyinf", "uid", $WLAN1, 0);
				$phyinf_wlan_p = XNODE_getpathbytarget("", "phyinf", "uid", $WLAN1_GZ, 0);
			}
			else if($WLAN_UID == $WLAN2_GZ)
			{
				$phyinf_wlan_host_p = XNODE_getpathbytarget("", "phyinf", "uid", $WLAN2, 0);
				$phyinf_wlan_p = XNODE_getpathbytarget("", "phyinf", "uid", $WLAN2_GZ, 0);
			}
			
			$phyinf_runtime_p = XNODE_getpathbytarget("/runtime","phyinf", "uid", $WLAN_UID, "0");
			
			$wifi_uid = query($phyinf_wlan_p."/wifi");
			$wifi_p = XNODE_getpathbytarget("/wifi", "entry", "uid", $wifi_uid, 0);
			
			$ACTIVE_PATH = $phyinf_wlan_p."/active";
			$active = query($ACTIVE_PATH);
			
			$AUTH_PATH = $wifi_p."/authtype";
			$authtype = query($AUTH_PATH);

			$ENCR_PATH = $wifi_p."/encrtype";
			$encrtype = query($ENCR_PATH);
			
			$WEP_PATH = $wifi_p."/nwkey/wep";
			
			if($NEXT_PARAM_NAME == "Enable" || $NEXT_PARAM_NAME == "RadioEnabled")
			{
				exec_by_type($ACTION, $ACTIVE_PATH, $SET_VALUE);
			}
			else if($NEXT_PARAM_NAME == "Status")
			{
				if($ACTION == "GET")
				{
					if($active == "1") { output("Up"); }
					else if($active == "0") { output("Disabled"); }
					else { output("Error"); }
				}
				else if($ACTION == "GET_PATH") { output($ACTIVE_PATH); }
			}
			else if($NEXT_PARAM_NAME == "Alias") { /* Not support now */ }
			else if($NEXT_PARAM_NAME == "Name")
			{
				if($ACTION == "GET")
				{
					if($WLAN_UID == $WLAN1) { output($BAND24G_DEVNAME); }
					else if($WLAN_UID == $WLAN2) { output($BAND5G_DEVNAME); }
					else if($WLAN_UID == $WLAN1_GZ) { output($BAND24G_GUEST_DEVNAME); }
					else if($WLAN_UID == $WLAN2_GZ) { output($BAND5G_GUEST_DEVNAME); }
				}
			}
			else if($NEXT_PARAM_NAME == "BSSID")
			{
				$WLAN_MAC_PATH = "/runtime/devdata/wlanmac";
				$WLAN2_MAC_PATH = "/runtime/devdata/wlanmac2";
				
				$wlan_mac = query($WLAN_MAC_PATH);
				$wlan2_mac = query($WLAN2_MAC_PATH);
				
				if($ACTION == "GET")
				{
					if($WLAN_UID == $WLAN1) { output($wlan_mac); }
					else if($WLAN_UID == $WLAN2) { output($wlan2_mac); }
					else if($WLAN_UID == $WLAN1_GZ) { output(get_guestzone_mac($wlan_mac)); }
					else if($WLAN_UID == $WLAN2_GZ) { output(get_guestzone_mac($wlan2_mac)); }
				}
				else if($ACTION == "GET_PATH")
				{
					if($WLAN_UID == $WLAN1) { output($WLAN_MAC_PATH); }
					else if($WLAN_UID == $WLAN2) { output($WLAN2_MAC_PATH); }
				}
			}
			else if($NEXT_PARAM_NAME == "MaxBitRate")
			{
				if($ACTION == "GET") { output("Auto"); } /* In current design, it always auto. */
			}
			else if($NEXT_PARAM_NAME == "Channel")
			{
				if($ACTION == "GET" || $ACTION == "GET_PATH")
				{
					$NODE_PATH = $phyinf_runtime_p."/media/channel";
				}
				else if($ACTION == "SET")
				{
					$NODE_PATH = $phyinf_wlan_host_p."/media/channel";
				}
				exec_by_type($ACTION, $NODE_PATH, $SET_VALUE);
			}
			else if($NEXT_PARAM_NAME == "AutoChannelEnable")
			{
				$NODE_PATH = $phyinf_wlan_host_p."/media/channel";
				if($ACTION == "GET")
				{
					$channel = query($NODE_PATH);
					if($channel == "0") { output("1"); }
					else { output("0"); }
				}
				else if($ACTION == "SET") { set($NODE_PATH, $SET_VALUE); }
				else if($ACTION == "GET_PATH") { output($NODE_PATH); }
			}
			else if($NEXT_PARAM_NAME == "SSID")
			{
				$NODE_PATH = $wifi_p."/ssid";
				exec_by_type($ACTION, $NODE_PATH, $SET_VALUE);
			}
			else if($NEXT_PARAM_NAME == "BeaconType"){ /* Not support now */ }
			else if($NEXT_PARAM_NAME == "MACAddressControlEnabled")
			{
				$NODE_PATH = "/acl/macctrl/policy";
				if($ACTION == "GET")
				{
					$policy = query($NODE_PATH);
					if($policy == "DISABLE") { output("0"); }
					else { output("1"); }
				}
				else if($ACTION == "SET")
				{
					if($SET_VALUE == "0") { set($NODE_PATH, "DISABLE"); }
					else if($SET_VALUE == "1") { set($NODE_PATH, "DROP"); }
				}
				else if($ACTION == "GET_PATH") { output($NODE_PATH); }
			}
			else if($NEXT_PARAM_NAME == "Standard")
			{
				$NODE_PATH = $phyinf_wlan_host_p."/media/wlmode";
				if($ACTION == "GET")
				{
					$wlanmode = query($NODE_PATH);
					if($wlanmode == "b") { output("b"); }
					else if($wlanmode == "g") { output("g-only"); }
					else if($wlanmode == "bg") { output("g"); }
					else if($wlanmode == "a") { output("a"); }
					else if(strstr($wlanmode, "n") != "" && strstr($wlanmode, "ac") == "") { output("n"); }
					else if(strstr($wlanmode, "ac") != ""){ output("ac"); }
				}
				else if($ACTION == "GET_PATH"){ output($NODE_PATH); }
			}
			else if($NEXT_PARAM_NAME == "WEPKeyIndex")
			{
				if($ACTION == "GET")
				{
					$wepkey_index = get_wepkey_index($WEP_PATH);
					output($wepkey_index);
				}
				else if($ACTION == "SET"){ /* Not support */ }
				else if($ACTION == "GET_PATH"){ output($WEP_PATH); }
			}
			else if($NEXT_PARAM_NAME == "KeyPassphrase")
			{
				$wepkey_index = get_wepkey_index($WEP_PATH);
				$NODE_PATH = $WEP_PATH."/key:".$wepkey_index;
				exec_by_type($ACTION, $NODE_PATH, $SET_VALUE);
			}
			else if($NEXT_PARAM_NAME == "WEPEncryptionLevel")
			{
				$NODE_PATH = $WEP_PATH."/size";
				if($ACTION == "GET")
				{
					if($encrtype == "WEP")
					{
						$key_size = query($NODE_PATH);
						if($key_size == "64") { output("40-bit"); }
						else if($key_size == "128") { output("104-bit"); }
					}
					else{ output("Disabled"); }
				}
				else if($ACTION == "GET_PATH"){ output($NODE_PATH); }
			}
			else if($NEXT_PARAM_NAME == "BasicEncryptionModes")
			{
				if($ACTION == "GET")
				{
					if($encrtype == "WEP"){ output("WEPEncryption"); }
					else { output("None"); }
				}
				else if($ACTION == "SET")
				{
					if($SET_VALUE == "WEPEncryption") { set($ENCR_PATH, "WEP"); }
					else{ set($ENCR_PATH, $SET_VALUE); } /* value should be: NONE or TKIP or AES or TKIP+AES */
				}
				else if($ACTION == "GET_PATH"){ output($ENCR_PATH); }
			}
			else if($NEXT_PARAM_NAME == "BasicAuthenticationMode")
			{
				if($ACTION == "GET")
				{
					if($authtype == "WPA" || $authtype == "WPA2" || $authtype == "WPA+2")
					{ 
						output("EAPAuthentication");
					}
					else if($authtype == "WPAPSK" || $authtype == "WPA2PSK" || $authtype == "WPA+2PSK")
					{
						output("SharedAuthentication");
					}
					else{ output("None"); }
				}
				else if($ACTION == "SET")
				{
					if($SET_VALUE == "EAPAuthentication"){ set($AUTH_PATH, "WPA+2"); }
					else if($SET_VALUE == "SharedAuthentication"){ set($AUTH_PATH, "WPA+2PSK"); }
					else{ set($AUTH_PATH, $SET_VALUE); } /* value should be: OPEN or SHARE or WEPAUTO */
				}
				else if($ACTION == "GET_PATH"){ output($AUTH_PATH); }
			}
			else if($NEXT_PARAM_NAME == "WPAEncryptionModes"){
				if($ACTION == "GET")
				{
					if( $encrtype == "TKIP" ) {	output("TKIPEncryption"); }
					else if( $encrtype == "AES" ) { output("AESEncryption"); }
					else if( $encrtype == "TKIP+AES" ) { output("TKIPandAESEncryption"); }
				}
				else if($ACTION == "SET")
				{
					if($SET_VALUE == "TKIPEncryption") { set($ENCR_PATH, "TKIP"); }
					else if($SET_VALUE == "AESEncryption"){ set($ENCR_PATH, "AES"); }
					else if($SET_VALUE == "TKIPandAESEncryption"){ set($ENCR_PATH, "TKIP+AES"); }
				}
				else if($ACTION == "GET_PATH"){ output($ENCR_PATH); }
			}
			else if($NEXT_PARAM_NAME == "WPAAuthenticationMode"){
				if($ACTION == "GET")
				{
					if($authtype == "WPA" || $authtype == "WPA2" || $authtype == "WPA+2")
					{
						output("EAPAuthentication");
					}
					else if($authtype == "WPAPSK" || $authtype == "WPA2PSK" || $authtype == "WPA+2PSK")
					{
						output("PSKAuthentication");
					}
				}
				else if($ACTION == "SET")
				{
					if($SET_VALUE == "EAPAuthentication") { set($AUTH_PATH, "WPA+2"); }
					else if($SET_VALUE == "PSKAuthentication"){ set($AUTH_PATH, "WPA+2PSK"); }
				}
				else if($ACTION == "GET_PATH"){ output($AUTH_PATH); }
			}
			else if($NEXT_PARAM_NAME == "IEEE11iEncryptionModes"){ /* Not support now */ }
			else if($NEXT_PARAM_NAME == "IEEE11iAuthenticationMode"){ /* Not support now */ }
			else if($NEXT_PARAM_NAME == "PossibleChannels")
			{
				if($WLAN_UID == $WLAN1 || $WLAN_UID == $WLAN1_GZ)
				{
					$NODE_PATH = "/runtime/freqrule/channellist/g";
				}
				else if($WLAN_UID == $WLAN2 || $WLAN_UID == $WLAN2_GZ)
				{
					$NODE_PATH = "/runtime/freqrule/channellist/a";
				}
				exec_by_type($ACTION, $NODE_PATH, $SET_VALUE);
			}
			else if($NEXT_PARAM_NAME == "BasicDataTransmitRates"){ /* Not support now */ }
			else if($NEXT_PARAM_NAME == "OperationalDataTransmitRates"){ /* Not support now */ }
			else if($NEXT_PARAM_NAME == "PossibleDataTransmitRates"){ /* Not support now */ }
			else if($NEXT_PARAM_NAME == "InsecureOOBAccessEnabled"){ /* Not support now */ }
			else if($NEXT_PARAM_NAME == "BeaconAdvertisementEnabled"){ /* Not support now */ }
			else if($NEXT_PARAM_NAME == "SSIDAdvertisementEnabled")
			{
				$NODE_PATH = $wifi_p."/ssidhidden";
				exec_by_type($ACTION, $NODE_PATH, $SET_VALUE);
			}
			else if($NEXT_PARAM_NAME == "TransmitPowerSupported"){ /* Not support now */ }
			else if($NEXT_PARAM_NAME == "TransmitPower"){ /* Not support now */ }
			else if($NEXT_PARAM_NAME == "AutoRateFallBackEnabled"){ /* Not support now */ }
			else if($NEXT_PARAM_NAME == "LocationDescription"){ /* Not support now */ }
			else if($NEXT_PARAM_NAME == "RegulatoryDomain"){ /* Not support now */ }
			else if($NEXT_PARAM_NAME == "TotalPSKFailures"){ /* Not support now */ }
			else if($NEXT_PARAM_NAME == "TotalIntegrityFailures"){ /* Not support now */ }
			else if($NEXT_PARAM_NAME == "ChannelsInUse")
			{
				$NODE_PATH = $phyinf_runtime_p."/media/channel";
				exec_by_type($ACTION, $NODE_PATH, $SET_VALUE);
			}
			else if($NEXT_PARAM_NAME == "DeviceOperationMode")
			{
				$NODE_PATH = $wifi_p."/opmode";
				if($ACTION == "GET"){
					$opmode = query($NODE_PATH);
					if($opmode == "AP") { output("InfrastructureAccessPoint"); }
				}
				else if($ACTION == "GET_PATH"){ output($NODE_PATH); }
			}
			else if($NEXT_PARAM_NAME == "DistanceFromRoot"){ /* Only for wireless repeater or bridge mode. */ }
			else if($NEXT_PARAM_NAME == "PeerBSSID"){ /* Only for wireless repeater or bridge mode. */ }
			else if($NEXT_PARAM_NAME == "AuthenticationServiceMode")
			{
				if($ACTION == "GET")
				{
					if($authtype == "WPA" || $authtype == "WPA2" || $authtype == "WPA+2")
					{
						output("RadiusClient");
					}
					else if($authtype == "OPEN" && $encrtype == "NONE")
					{
						output("None");
					}
					else { output("LinkAuthentication"); }
				}
				else if($ACTION == "SET"){ /* Not support now */ }
				else if($ACTION == "GET_PATH"){ output($AUTH_PATH); }
			}
			else if($NEXT_PARAM_NAME == "WMMSupported")
			{
				if($ACTION == "GET"){ output("1"); }
			}
			else if($NEXT_PARAM_NAME == "UAPSDSupported"){ /* Not support now */ }
			else if($NEXT_PARAM_NAME == "WMMEnable")
			{
				$NODE_PATH = $phyinf_wlan_host_p."/media/wmm/enable";
				exec_by_type($ACTION, $NODE_PATH, $SET_VALUE);
			}
			else if($NEXT_PARAM_NAME == "UAPSDEnable"){ /* Not support now */ }
			else if($NEXT_PARAM_NAME == "TotalBytesSent")
			{
				$NODE_PATH = $phyinf_runtime_p."/stats/tx/bytes";
				exec_by_type($ACTION, $NODE_PATH, $SET_VALUE);
			}
			else if($NEXT_PARAM_NAME == "TotalBytesReceived")
			{
				$NODE_PATH = $phyinf_runtime_p."/stats/rx/bytes";
				exec_by_type($ACTION, $NODE_PATH, $SET_VALUE);
			}
			else if($NEXT_PARAM_NAME == "TotalPacketsSent")
			{
				$NODE_PATH = $phyinf_runtime_p."/stats/tx/packets";
				exec_by_type($ACTION, $NODE_PATH, $SET_VALUE);
			}
			else if($NEXT_PARAM_NAME == "TotalPacketsReceived")
			{
				$NODE_PATH = $phyinf_runtime_p."/stats/rx/packets";
				exec_by_type($ACTION, $NODE_PATH, $SET_VALUE);
			}
			else if($NEXT_PARAM_NAME == "TotalAssociations")
			{
				$NODE_PATH = $phyinf_runtime_p."/media/clients";
				if($ACTION == "GET"){ output(query($NODE_PATH."/entry#")); }
				else if($ACTION == "GET_PATH"){ output($NODE_PATH); }
			}
			
			if ($ACTION == "SET")
			{
				fwrite("w", $EXEC_SHELL_FILE, "#!/bin/sh \n"); 
				fwrite("a", $EXEC_SHELL_FILE, "service ".$SRVC_WLAN." restart \n"); 
			}
		}
	}
}
?>