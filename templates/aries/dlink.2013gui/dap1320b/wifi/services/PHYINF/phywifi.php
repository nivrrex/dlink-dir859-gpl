<?
include "/htdocs/phplib/phyinf.php";
include "/htdocs/phplib/xnode.php";
include "/htdocs/phplib/trace.php";

$UID24G	= "BAND24G";
$UID5G	= "BAND5G"; 

function startcmd($cmd)	{fwrite(a,$_GLOBALS["START"], $cmd."\n");}
function stopcmd($cmd)	{fwrite(a,$_GLOBALS["STOP"], $cmd."\n");}
function error($err)	{startcmd("exit ".$err); stopcmd("exit ".$err); return $err;}

/**********************************************************************/
function devname($uid)
{
	if ($uid=="BAND24G-1.1")	return "ra0";
	if ($uid=="BAND24G-1.2")	return "ra1";
	if ($uid=="BAND24G-1.3")	return "apcli0";
	return "";
}

/* what we check ?
1. if host is disabled, then our guest must also be disabled !!
*/
function host_guest_dependency_check($uid)
{
	if($uid == $_GLOBALS["UID24G"]."-1.2") 			$host_uid = $_GLOBALS["UID24G"]."-1.1";
	else if ($uid == $_GLOBALS["UID5G"]."-1.2")		$host_uid = $_GLOBALS["UID5G"]."-1.1";
	else return 1;
	
	$p = XNODE_getpathbytarget("", "phyinf", "uid", $host_uid, 0);
	if (query($p."/active")!=1) return 0;
	else 						return 1;
}

function isguestzone($uid)
{
	$postfix = cut($uid, 1,"-");
	$minor = cut($postfix, 1,".");
	if($minor=="2")	return 1;
	else			return 0;
}
function find_brdev($phyinf)
{
	foreach ("/runtime/phyinf")
	{
		if (query("type")!="eth") continue;
		foreach ("bridge/port") if ($VaLuE==$phyinf) {$find = "yes"; break;}
		if ($find=="yes") return query("name");
	}
	return "";
}

function wifi_service($wifi_uid)
{
	$dev = devname($wifi_uid);
	$clean_output = " 1>/dev/null 2>&1";
	$prefix = cut($wifi_uid, 0,"-");
	if ($prefix=="BAND24G") $drv="RT2860";
	if ($prefix=="BAND5G") $drv="RT2860AP";

	startcmd("xmldbc -P /etc/services/WIFI/rtcfg.php -V PHY_UID=".$wifi_uid." > /var/run/".$drv.".dat");
}

function wificonfig($uid)
{
    fwrite(w,$_GLOBALS["START"], "#!/bin/sh\n");
	fwrite(w,$_GLOBALS["STOP"],  "#!/bin/sh\n");
//	fwrite("a",$_GLOBALS["START"], "killall hostapd > /dev/null 2>&1;\n");
//	fwrite("a",$_GLOBALS["STOP"], "killall hostapd > /dev/null 2>&1;\n");
	
	$brdev = find_brdev($uid);
	$dev	= devname($uid);
	$prefix = cut($uid, 0,"-");
	if($prefix==$_GLOBALS["UID24G"]) $drv="WIFI";	
	if($prefix==$_GLOBALS["UID5G"]) $drv="WIFI_5G";	
	if($prefix=="WIFI") 		$drv="WIFI";	

	$infp = XNODE_getpathbytarget("", "inf", "uid", "BRIDGE-1", 0);
	$p = XNODE_getpathbytarget("", "phyinf", "uid", $uid, 0);
	if ($p=="" || $drv=="" || $dev=="")		return error(9);
	if (query($p."/active")!=1) return error(8);
	$wifi = XNODE_getpathbytarget("/wifi",  "entry",  "uid", query($p."/wifi"), 0);
	$opmode = query($wifi."/opmode");
	$freq   = query($p."/media/freq");
	$isgzone = isguestzone($uid);

	if(host_guest_dependency_check($uid)==0)	return error(8);

	

	$wlan1=PHYINF_setup($uid, "wifi", $dev);
	$dtype  = "urn:schemas-wifialliance-org:device:WFADevice:1";
	setattr("/runtime/hostapd/mac",  "get", "devdata get -e lanmac");
	setattr("/runtime/hostapd/guid", "get", "genuuid -s \"".$dtype."\" -m \"".query("/runtime/hostapd/mac")."\"");


	if($opmode == "AP")
	{
		startcmd("xmldbc -k \"HOSTAPD_RESTARTAP\"");
		wifi_service($uid);
	}
//	else if($opmode == "REPEATER" || $opmode == "STA")
//	{
//		wifi_client_service($uid);
//	}

	startcmd("ip link set ".$dev." up");
	startcmd("brctl addif ".$brdev." ".$dev);

	startcmd("rm -f /var/run/".$uid.".DOWN");
	startcmd("echo 1 > /var/run/".$uid.".UP");

	stopcmd("ip link set ".$dev." down");
	stopcmd("brctl delif ".$brdev." ".$dev);
	stopcmd("echo 1 > /var/run/".$uid.".DOWN");
	stopcmd("rm -f /var/run/".$uid.".UP");

	stopcmd("phpsh /etc/scripts/delpathbytarget.php BASE=/runtime NODE=phyinf TARGET=uid VALUE=".$uid);

	/* define WFA related info for hostapd */
	startcmd("phpsh /etc/scripts/wpsevents.php ACTION=ADD"); 
	startcmd("phpsh /etc/scripts/wifirnodes.php UID=".$uid);

	if($opmode == "AP")
	{
		/* +++ upwifistats */
		startcmd("xmldbc -P /etc/services/WIFI/updatewifistats.php -V PHY_UID=".$prefix."-1.1 > /var/run/restart_upwifistats.sh");
		startcmd("sh /var/run/restart_upwifistats.sh");
		stopcmd("xmldbc -P /etc/services/WIFI/updatewifistats.php -V PHY_UID=".$prefix."-1.1 > /var/run/restart_upwifistats.sh");
		stopcmd("sh /var/run/restart_upwifistats.sh");
		/* --- upwifistats */

		/*if enable gzone this action will run 4 time when wifi restart.
		  we pending this action in 5 seconds..................
		  all restart actions in 5 seconds ,we just run 1 time....
		*/
		startcmd("xmldbc -t \"HOSTAPD_RESTARTAP:3:sh /etc/scripts/restartap_hostapd.sh\"");
		stopcmd("killall hostapd > /dev/null 2>&1");
		stopcmd("phpsh /etc/services/WIFI/interfacereboot.php UID=".$uid."");
		startcmd("service MULTICAST restart");
		stopcmd("service MULTICAST restart");
	}
	else if($opmode == "REPEATER"){
		$path_br = XNODE_getpathbytarget("", "inf", "uid", "BRIDGE-1", 0);
		$phyuid_br = query($path_br."/phyinf");
		$rbrphy =  XNODE_getpathbytarget("/runtime", "phyinf", "uid", $phyuid_br, 0);
		//setattr($wlan1."/media/status",  "get", "usockc /var/gpio_ctrl APC_STATE;cat /var/connect_state");
		setattr($rbrphy."/linkstatus",  "get", "usockc /var/gpio_ctrl APC_STATE;cat /var/connect_state");
	}
	startcmd("event BRIDGE-1.DHCP.RENEW");

	stopcmd("event BRIDGE-1.DHCP.RELEASE");
	stopcmd("event WPS.NONE");
	return error(0);
}
?>
