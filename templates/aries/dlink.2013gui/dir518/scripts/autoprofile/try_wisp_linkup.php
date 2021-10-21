<?
include "/htdocs/webinc/config.php";
include "/htdocs/phplib/xnode.php";
include "/htdocs/phplib/phyinf.php";
include "/htdocs/phplib/trace.php";

$sitesurvey_5G_e = "/runtime/wifi_tmpnode/sitesurvey_5G/entry";
$sitesurvey_24G_e = "/runtime/wifi_tmpnode/sitesurvey_24G/entry";
$profile_e = "/internetprofile/entry";
$wispstatus = "/runtime/internetprofile/wispstatus";

function get_last_uid($path, $protype)
{
	$lastuid = "";
	foreach($path)
	{
		$type = query("profiletype");
		if($type==$protype)
		{
			$lastuid = query("uid");
		}
	}
	return $lastuid;
}

function find_sslist_path($path, $ssid)
{
	$p_index = 0;
	foreach($path)
	{
		$s_ssid = query("ssid");
		if($ssid==$s_ssid)
		{
			$p_index = $InDeX;
			break;
		}
	}
	return $p_index;
}

if($ACTIVEPRO!="")
{
	$pro_path = XNODE_getpathbytarget("internetprofile", "entry", "uid", $ACTIVEPRO, 0);
	$pro_type = query($pro_path."/profiletype");
	
	$ssid = query($pro_path."/profilename");
	$entry_no = find_sslist_path($sitesurvey_5G_e, $ssid);
	
	if($entry_no < 1)
	{
		$entry_no = find_sslist_path($sitesurvey_24G_e, $ssid);
		
		if($entry_no < 1) { /*not found*/ }
		else
		{
			$path = $sitesurvey_24G_e.":".$entry_no;
			$band = $WLAN1_REP;
		}
	}
	else
	{
		$path = $sitesurvey_5G_e.":".$entry_no;
		$band = $WLAN2_REP;
	}
	
	//------function need....
	$authtype = query($path."/authtype");
	$encrtype = query($path."/encrtype");
	
	if($authtype=="")
	{
		if($encrtype=="NONE")//NONE
		{
			$authtype = "OPEN";
			$encrtype = "NONE";
			$cmd = "phpsh /etc/scripts/wisp.php ACTION=WISP_DETECT uid=".$band." ssid=".$ssid." authtype=".$authtype." encrtype=".$encrtype;
		}
		else if($encrtype=="WEP") //WEP
		{
			$authtype = "WEPAUTO";
			$encrtype = "WEP";
			$wepkey = query($pro_path."/config/wep/key");
			$wepkeylen = query($pro_path."/config/wep/size");
			$ascii = query($pro_path."/config/wep/ascii");
			$cmd = "phpsh /etc/scripts/wisp.php ACTION=WISP_DETECT uid=".$band." ssid=".$ssid." authtype=".$authtype." encrtype=".$encrtype." wep_key=".$wepkey." wep_key_len=".$wepkeylen." ascii=".$ascii;
		}
	}
	else if(strstr($authtype, "WPA")!="") //WPA
	{
		if(strstr($authtype, "PSK")!="")
		{
			if($authtype=="WPA+2PSK") { $authtype = "WPA2PSK"; }
			
			if($encrtype=="TKIP+AES") { $encrtype = "AES"; }
			
			$psk = query($pro_path."/config/psk/key");
			$cmd = "phpsh /etc/scripts/wisp.php ACTION=WISP_DETECT uid=".$band." ssid=".$ssid." authtype=".$authtype." encrtype=".$encrtype." psk=".$psk;
		}
		else { $status = "NOTSUPPORT"; }
	}
	echo $cmd.'\n';
	
	set("/runtime/internetprofile/wispstatus/now", $ACTIVEPRO);
	set("/runtime/internetprofile/wispstatus/band", $band);
	set("/runtime/internetprofile/wispstatus/status", "TRYLINKUP");
}
else
{
	$start_indx = 0;
	$wisplastone = get_last_uid($profile_e, "WISP");
	$testnow_path = $wispstatus."/now";
	$band_path = $wispstatus."/band";
	$lastone_path = $wispstatus."/lastone";
	$status_path = $wispstatus."/status";
	$lastone = query($lastone_path);
	$status = "TRYLINKUP";
	
	TRACE_debug("$wisplastone=".$wisplastone."\n".
							"$lastone=".$lastone);
	
	if($lastone=="") {$lastone=0;}
	else {$lastone = $lastone;}
	
	if($lastone==1)
	{
		$status = "LASTONE";
		set($status_path, $status);
		exit ;
	}
	else if($lastone==0)
	{
		$nowwisp = query("/runtime/internetprofile/wispstatus/now");
		
		if($nowwisp!="")
		{
			foreach($profile_e)
			{
				$uid = query("uid");
				if($nowwisp==$uid)
				{
					$start_indx = $InDeX;
					break;
				}
			}
		}
		else { $start_indx = 0; }
		
		TRACE_debug("$start_indx=".$start_indx);
		
		foreach($profile_e)
		{
			$type = query("profiletype");
			if($type=="WISP" && $InDeX > $start_indx)
			{
				$uid = query("uid");
				if($uid==$wisplastone) { set($lastone_path, 1); }
				else { set($lastone_path, 0); }
				
				$ssid = query("profilename");
				$entry_no = find_sslist_path($sitesurvey_5G_e, $ssid);
				
				if($entry_no < 1)
				{
					$entry_no = find_sslist_path($sitesurvey_24G_e, $ssid);
					
					if($entry_no < 1) { $status = "NOTFOUND"; }
					else
					{
						$band = $WLAN1_REP;
						$i_path = $profile_e.":".$InDeX;
						$s_path = $sitesurvey_24G_e.":".$entry_no;
					}
				}
				else
				{
					$band = $WLAN2_REP;
					$i_path = $profile_e.":".$InDeX;
					$s_path = $sitesurvey_5G_e.":".$entry_no;
				}
				break;
			}
		}
		TRACE_debug("$uid=".$uid."\n".
								"$ssid=".$ssid."\n".
								"$band=".$band."\n".
								"$i_path=".$i_path."\n".
								"$s_path=".$s_path);
		
		set($testnow_path, $uid);
		set($band_path, $band);
		
		$authtype = query($s_path."/authtype");
		$encrtype = query($s_path."/encrtype");
		
		if($authtype=="")
		{
			if($encrtype=="NONE")//NONE
			{
				$authtype = "OPEN";
				$encrtype = "NONE";
				$cmd = "phpsh /etc/scripts/wisp.php ACTION=WISP_DETECT uid=".$band." ssid=".$ssid." authtype=".$authtype." encrtype=".$encrtype;
			}
			else if($encrtype=="WEP") //WEP
			{
				$authtype = "WEPAUTO";
				$encrtype = "WEP";
				$wepkey = query($i_path."/config/wep/key");
				$wepkeylen = query($i_path."/config/wep/size");
				$ascii = query($i_path."/config/wep/ascii");
				$cmd = "phpsh /etc/scripts/wisp.php ACTION=WISP_DETECT uid=".$band." ssid=".$ssid." authtype=".$authtype." encrtype=".$encrtype." wep_key=".$wepkey." wep_key_len=".$wepkeylen." ascii=".$ascii;
			}
		}
		else if(strstr($authtype, "WPA")!="") //WPA
		{
			if(strstr($authtype, "PSK")!="")
			{
				if($authtype=="WPA+2PSK") { $authtype = "WPA2PSK"; }
				
				if($encrtype=="TKIP+AES") { $encrtype = "AES"; }
				
				$psk = query($i_path."/config/psk/key");
				$cmd = "phpsh /etc/scripts/wisp.php ACTION=WISP_DETECT uid=".$band." ssid=".$ssid." authtype=".$authtype." encrtype=".$encrtype." psk=".$psk;
			}
			else { $status = "NOTSUPPORT"; }
		}
		echo $cmd.'\n';
	}
	
	set($status_path, $status);
}
?>
