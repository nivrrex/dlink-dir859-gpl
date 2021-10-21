#!/bin/sh
<?
include "/htdocs/phplib/trace.php";
include "/htdocs/phplib/xnode.php";
include "/etc/services/WIFI/function.php";

function update_state($uid, $state)
{
	$p = XNODE_getpathbytarget("/runtime", "phyinf", "uid", $uid, 0);
	if ($p != "") set($p."/media/wps/enrollee/state", $state);
}

function kill_wpatalk($uid)
{
	$pidfile = "/var/run/wpatalk.".$uid.".pid";
	$pid = fread("s", $pidfile);
	if ($pid != "")
	{
		echo "kill ".$pid."\n";
		echo "rm ".$pidfile."\n";
	}
}

function do_wps($uid, $method)
{
	$p = XNODE_getpathbytarget("", "phyinf", "uid", $uid, 0);
	if ($p == "") return;
	if (query($p."/active")!="1") return;
	$p = XNODE_getpathbytarget("/wifi", "entry", "uid", query($p."/wifi"), 0);
	if ($p == "") return;
	$enable = query($p."/wps/enable");
	if ($enable!="1") return;

	$p = XNODE_getpathbytarget("/runtime", "phyinf", "uid", $uid, 0);
	if ($p == "") return;
	
	update_state($uid, "WPS_IN_PROGRESS");
	event("WPS.INPROGRESS");
	
	$dev = query($p."/name");
	$pin = query($p."/media/wps/enrollee/pin");

	if		($method == "pbc") $cmd = "configthem";
	else if ($method == "pin") $cmd = "\"configthem pin=".$pin."\"";
	else return;

	kill_wpatalk($uid);
	$pidfile = "/var/run/wpatalk.".$uid.".pid";
	echo "wpatalk ".$dev." ".$cmd." &\n";
	echo "echo $! > ".$pidfile."\n";
}

function stop_wps($uid)
{
	$p = XNODE_getpathbytarget("/runtime", "phyinf", "uid", $uid, 0);
	if ($p == "") return;
	anchor($p);
	$dev = query("name");
	echo "wpatalk ".$dev." configstop &\n";
	kill_wpatalk($uid);
}

function set_wps($uid)
{
	TRACE_debug("SETWPS(".$uid."):\n");
	
	/* Validating the interface. */
	if ($uid=="")	{TRACE_debug("SETWPS: error - no UID!\n"); return;}
	$p = XNODE_getpathbytarget("", "phyinf", "uid", $uid, 0);
	if ($p=="")		{TRACE_debug("SETWPS: error - no PHYINF!\n"); return;}
	$wifi = query($p."/wifi");
	if ($wifi=="")	{TRACE_debug("SETWPS: error - no wifi!\n"); return;}
	$p = XNODE_getpathbytarget("/wifi", "entry", "uid", $wifi, 0);
	if ($p=="")		{TRACE_debug("SETWPS: error - no wifi profile!\n"); return;}
	
	/* The WPS result. */
	anchor("/runtime/wps/setting");
	$scfg	= query("selfconfig");	TRACE_debug("selfconf	= ".$scfg);
	$ssid	= query("ssid");		TRACE_debug("ssid		= ".$ssid);
	$atype	= query("authtype");	TRACE_debug("authtype	= ".$atype);
	$etype	= query("encrtype");	TRACE_debug("encrtype	= ".$etype);
	$defkey	= query("defkey");		TRACE_debug("defkey		= ".$defkey);
	$maddr	= query("macaddr");		TRACE_debug("macaddr	= ".$maddr);
	$newpwd	= query("newpassword");	TRACE_debug("newpwd		= ".$newpwd);
	$devpid	= query("devpwdid");	TRACE_debug("devpwdid	= ".$devpid);
	
	/* If we started from Unconfigured AP (self configured),
	 * change the setting to auto. */
	if		($scfg == 1)	{ $atype = 5; $etype = 3; /* WPA2 PSK & AES */ }
	
	if		($atype == 0)	$atype = "OPEN";
	else if ($atype == 1)	$atype = "SHARED";
	else if ($atype == 2)	$atype = "WPA";
	else if ($atype == 3)	$atype = "WPAPSK";
	else if ($atype == 4)	$atype = "WPA2";
	else if ($atype == 5)	$atype = "WPA2PSK";
	else if ($atype == 6)	$atype = "WPA+2";
	else if ($atype == 7)	$atype = "WPA+2PSK";
	
	if		($etype == 0)	$etype = "NONE";
	else if ($etype == 1)	$etype = "WEP";
	else if ($etype == 2)	$etype = "TKIP";
	else if ($etype == 3)	$etype = "AES";
	else if ($etype == 4)	$etype = "TKIP+AES";
	
	set($p."/ssid",		$ssid);
	set($p."/authtype",	$atype);
	set($p."/encrtype",	$etype);

	if ($etype=="WEP")
	{
		foreach ("key")
		{
			TRACE_debug("key[".$InDeX."]");
			$idx = query("index");	TRACE_debug("key index	= ".$idx);
			$key = query("key");	TRACE_debug("key		= ".$key);
			$fmt = query("format");	TRACE_debug("format		= ".$fmt);
			$len = query("len");	TRACE_debug("len		= ".$len);
			
			if ($idx<5 && $idx>0) set($p."/nwkey/wep/key:".$idx, $key);
		}
		
		if ($fmt!=1) $fmt=0;
		set($p."/nwkey/wep/defkey",	$idx);
		set($p."/nwkey/wep/ascii",	$fmt);
		/*
		 *	Ascii	 64 bits ->  5 bytes
		 *			128 bits -> 13 bytes
		 *	Hex		 64 bits -> 10 bytes
		 *			128 bits -> 26 bytes
		 *
		 * size should be filled with "64" and "128", so we derive it from above.
		 */
		if		($len==5  || $len==10)	set($p."/nwkey/wep/size", "64");
		else if ($len==13 || $len==26)	set($p."/nwkey/wep/size", "128");
		else set($p."/nwkey/wep/size", "64"); //just for default
	}
	else
	{
		/* The 2st key only. */
		$idx = query("key:1/index");	TRACE_debug("key index	= ".$idx);
		$key = query("key:1/key");		TRACE_debug("key		= ".$key);
		$fmt = query("key:1/format");	TRACE_debug("format		= ".$fmt);
		$len = query("key:1/len");		TRACE_debug("len		= ".$len);
		if ($fmt!=1) $fmt=0;
		set($p."/nwkey/psk/passphrase", $fmt);
		set($p."/nwkey/psk/key", $key);
	}
	
	set($p."/wps/configured", "1");
}

function kill_wscd($dev)
{
	$pidfile = "/var/run/wscd.".$dev.".pid";
	$pid = fread("s", $pidfile);
	if ($pid != "")
	{
		echo "kill ".$pid."\n";
		echo "rm ".$pidfile."\n";
	}
}

function wps_interface_up($uid)
{
	$p = XNODE_getpathbytarget("", "phyinf", "uid", $uid, 0);
	if ($p == "") return;
	if (query($p."/active")!="1") 
	{
		set($p."/active","1");
	}
	$p = XNODE_getpathbytarget("/wifi", "entry", "uid", query($p."/wifi"), 0);
	if ($p == "") return;
	$enable = query($p."/wps/enable");
	if ($enable!="1") return;

	$p = XNODE_getpathbytarget("/runtime", "phyinf", "uid", $uid, 0);
	if ($p == "") return;
	$dev = query($p."/name");

//	echo 'iwpriv '.$dev.' set_mib wsc_enable=1\n';
//	if(isfile("/var/run/".$dev.".UP") != 1)
//	{
		echo 'iwpriv '.$dev.' set_mib func_off=0\n';
		echo 'ifconfig '.$dev.' up\n';
//	}
}
function do_wps_sta($uid, $method)
{
	$p = XNODE_getpathbytarget("", "phyinf", "uid", $uid, 0);
	if ($p == "") return;
	if (query($p."/active")!="1") return;
	$p = XNODE_getpathbytarget("/wifi", "entry", "uid", query($p."/wifi"), 0);
	if ($p == "") return;
	$enable = query($p."/wps/enable");
	if ($enable!="1") return;

	$p = XNODE_getpathbytarget("/runtime", "phyinf", "uid", $uid, 0);
	if ($p == "") return;
	
	update_state($uid, "WPS_IN_PROGRESS");
	event("WPS.INPROGRESS");
	
	$dev = query($p."/name");
	$pin = query($p."/media/wps/enrollee/pin");

	if		($method == "pbc") $cmd = "-sig_pbc ".$dev;
	else if ($method == "pin") $cmd = "-sig_start ".$dev;
	else return;

	echo "rm /var/run/".$uid."WPS.DONE\n";
	//kill_wscd($dev);
//	echo "wscd -stop ".$dev."\n";
	echo "wscd ".$cmd."\n";
//	$pidfile = "/var/run/wpatalk.".$uid.".pid";
//	echo "echo $! > ".$pidfile."\n";
}

function stop_wps_sta($uid)
{
	$p = XNODE_getpathbytarget("/runtime", "phyinf", "uid", $uid, 0);
	if ($p == "") return;
	anchor($p);
	$dev = query("name");
//	kill_wscd($dev);
	echo "wscd -stop ".$dev."\n";
	echo "ifconfig ".$dev." down\n";
}

function set_wps_save($uid,$mode)
{
	$profile_p = "/internetprofile";
	$profile_e = $profile_p."/entry";
	$seqno = get("x", "/internetprofile/seqno");
	$count = get("x", "/internetprofile/count");

	$pro_path = XNODE_getpathbytarget("/internetprofile", "entry", "profilename", $_GLOBALS["SSID"], 0);
	
	if($pro_path!="") /* if the profile exits. */
	{
		$pro_path=$pro_path;
	}
	else
	{
		$current_c = get("x", $profile_e."#");
		$current_c = $current_c + 1;
		$pro_path = $profile_e.":".$current_c;	
		set($pro_path."/uid", "PRO-".$current_c);
	}
	set($pro_path."/profilename", $_GLOBALS["SSID"]);
	set($pro_path."/profiletype", "WISP");
	anchor($pro_path."/config");

	if($_GLOBALS["AUTHMODE"]!="")
	{
		set("authtype",$_GLOBALS["AUTHMODE"]);
		set("encrtype",$_GLOBALS["ENCRTYPE"]);
		
		$len = strlen($_GLOBALS["KEYSTR"]);
		
		if($_GLOBALS["ENCRTYPE"]=="WEP")
		{
			set("wep/defkey",$_GLOBALS["KEYINDEX"]);
			set("wep/key:".$_GLOBALS["KEYINDEX"] ,$_GLOBALS["KEYSTR"]);
			/*
			 *	Ascii	 64 bits ->  5 bytes
			 *			128 bits -> 13 bytes
			 *	Hex		 64 bits -> 10 bytes
			 *			128 bits -> 26 bytes
			 *
			 * size should be filled with "64" and "128", so we derive it from above.
			 */
			if		($len==5 || $len==10) {set("wep/size", "64");}
			else if ($len==13 || $len==26) {set("wep/size", "128");}
			else {set("wep/size", "64");} //just for default
			
			if ($len==5  || $len==13) {set("wep/ascii", "1");}
			else {set("wep/ascii", "0");}
		}
		else if($_GLOBALS["ENCRTYPE"]=="TKIP" || $_GLOBALS["ENCRTYPE"]=="AES" || $_GLOBALS["ENCRTYPE"]=="TKIP+AES")
		{
			set("authtype","WPA+2PSK");
			set("encrtype","TKIP+AES");
			if($len == 64) { $passphrase = 0;}
			else 		   { $passphrase = 1;}
			
			set($wifi."psk/key", $_GLOBALS["KEYSTR"]);
			set($wifi."psk/passphrase", $passphrase);
		}
	}
	$cnt = get("x", $profile_e."#");
	set($profile_p."/seqno", $cnt+1);
	set($profile_p."/count", $cnt);
	echo "echo 1 > /var/run/".$uid."WPS.DONE\n";
}
/************************************************************************/
$UID24G = "BAND24G-1.1";
$UID5G = "BAND5G-1.1";
$UIDSTA = "BAND24G-REPEATER";
$UIDSTA5G = "BAND5G-REPEATER";

if ($PARAM1=="pin" || $PARAM1=="pbc")
{
	TRACE_debug("PIN/PBC:".$PARAM1);
//	update_state($UID24G, "WPS_IN_PROGRESS");
//	update_state($UID5G, "WPS_IN_PROGRESS");
//	event("WPS.INPROGRESS");
	wps_interface_up($UIDSTA5G);
	wps_interface_up($UIDSTA);
	echo "sleep 1\n";

	if ($PARAM2 == "STA" || $PARAM2 == "REPEATER")
	{
		set("/runtime/wps_sta/state", "WPS.READY");
		echo 'echo 1 > /var/wps_start_pbc\n';
		echo 'usockc /var/wanmonitor_ctrl WISP_NOTREADY\n';
		echo 'xmldbc -k "WIFI_SELECT"\n';
		echo 'xmldbc -t "WIFI_SELECT:120:event WPS.STOP"\n';

		do_wps_sta($UIDSTA5G, $PARAM1);
		do_wps_sta($UIDSTA, $PARAM1);
	}
	else
	{
		do_wps($UID24G, $PARAM1);
		do_wps($UID5G, $PARAM1);
	}
}
else if ($PARAM1 == "save")
{
	echo 'xmldbc -k "WIFI_SELECT"\n';
	if ($BAND == "24G")
	{
		set_wps_save($UIDSTA,$PARAM2);
	}
	else if ($BAND == "5G")
	{
		set_wps_save($UIDSTA5G,$PARAM2);
	}
	echo 'xmldbc -k "WPSSTA"\n';
	echo 'xmldbc -t "WPSSTA:3:killall wscd; event DBSAVE; service PHYINF.WIFI restart"\n';
}
else if ($PARAM1 == "stop")
{
	if ($PARAM2 == "STA" || $PARAM2 == "REPEATER")
	{
		echo 'xmldbc -k "WIFI_SELECT"\n';
		stop_wps_sta($UIDSTA5G);
		stop_wps_sta($UIDSTA);
		echo 'rm /var/wps_start_pbc\n';
		echo 'usockc /var/wanmonitor_ctrl WISP_READY\n';
	}
	else
	{
		stop_wps($UID24G);
		stop_wps($UID5G);
	}
}
else if ($PARAM1=="restartap")
{
	set_wps($UID24G);
	set_wps($UID5G);
	event("DBSAVE");
//	echo 'event REBOOT\n';
	echo 'xmldbc -t "WPS:1:service PHYINF.WIFI restart"\n';
}
else if ($PARAM1=="WPS_NONE")			{update_state($UID,"WPS_NONE");			event("WPS.NONE");}
else if ($PARAM1=="WPS_IN_PROGRESS")	{update_state($UID,"WPS_IN_PROGRESS");	event("WPS.INPROGRESS");}
else if ($PARAM1=="WPS_ERROR")			{update_state($UID,"WPS_ERROR");		event("WPS.ERROR");}
else if ($PARAM1=="WPS_OVERLAP")		{update_state($UID,"WPS_OVERLAP");		event("WPS.OVERLAP");}
else if ($PARAM1=="WPS_SUCCESS")
{
	update_state($UID24G, "WPS_SUCCESS");
	update_state($UID5G, "WPS_SUCCESS");
	event("WPS.SUCCESS");
	kill_wpatalk($UID24G);
	kill_wpatalk($UID5G);
	
	stop_wps_sta($UIDSTA5G);
	stop_wps_sta($UIDSTA);
	echo 'rm /var/wps_start_pbc\n';
	echo 'usockc /var/wanmonitor_ctrl WISP_READY\n';
}
else
{
	$err = "usage: wps.sh [pin|pbc|WPS_NONE|WPS_IN_PROGRESS|WPS_ERROR|WPS_OVERLAP|WPS_SUCCESS]";
	echo 'echo "'.$err.'" > /dev/console\n';
}
?>
