<?
include "/htdocs/phplib/xnode.php";
include "/htdocs/webinc/config.php";
include "/htdocs/phplib/trace.php";

echo "#!/bin/sh\n";

/*
 * $PROUID = profile uid.
 */

/* internet */
$wan1_path = XNODE_getpathbytarget("", "inf", "uid", $WAN1, 0);
$wan1_inet = query($wan1_path."/inet");
$wan1_phyinf = query($wan1_path."/phyinf");
$wan1_inet_path = XNODE_getpathbytarget("/inet", "entry", "uid", $wan1_inet, 0);
$wan1_phyinf_path = XNODE_getpathbytarget("", "phyinf", "uid", $wan1_phyinf, 0);
$wan2_inet = query($wan2_path."/inet");
$wan2_path = XNODE_getpathbytarget("", "inf", "uid", $WAN2, 0);
$wan2_inet_path = XNODE_getpathbytarget("/inet", "entry", "uid", $wan2_inet, 0);

/* wifi */
$wan7_path = XNODE_getpathbytarget("", "inf", "uid", $WAN7, 0);
$wan7_phyinf = query($wan7_path."/phyinf");
$wan7_phyinf_path = XNODE_getpathbytarget("", "phyinf", "uid", $wan7_phyinf, 0);
$wan7_wifi = query($wan7_phyinf_path."/wifi");
$wan7_wifi_path = XNODE_getpathbytarget("/wifi", "entry", "uid", $wan7_wifi, 0);

/* 3g */
$wan3_path = XNODE_getpathbytarget("", "inf", "uid", $WAN3, 0);
$wan3_phyinf = query($wan3_path."/phyinf");
$wan3_inet = query($wan3_path."/inet");
$wan3_inet_path = XNODE_getpathbytarget("/inet", "entry", "uid", $wan3_inet, 0);

if($PROUID=="PRO-3GAUTO")
{
	$pro_path = "/runtime/auto_config";
	$pro_type = "USB3G";
}
else
{
	$pro_path = XNODE_getpathbytarget("/internetprofile", "entry", "uid", $PROUID, 0);
	$pro_name = query($pro_path."/profilename");
	$pro_type = query($pro_path."/profiletype");
}

/* save active */
$r_p = XNODE_getpathbytarget("/runtime", "internetprofile/entry", "profileuid", $PROUID, 0);
foreach("/runtime/internetprofile/entry")
{
	set("active", 0);
}
set($r_p."/active", 1);

$sitesurvey_5G_e = "/runtime/wifi_tmpnode/sitesurvey_5G/entry";
$sitesurvey_24G_e = "/runtime/wifi_tmpnode/sitesurvey_24G/entry";

function findlist($path, $ssid)
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

function clear_dns_entry($inetp)
{
	$dns_cnt = get("",$inetp."/ipv4/dns/count");
	while ($dns_cnt > 0)
	{
		del($inetp."/ipv4/dns/entry:1");
		$dns_cnt = $dns_cnt - 1;
	}
	set($inetp."/ipv4/dns/count", 0);
}

if($pro_type=="DHCP")
{
	$hostname = query($pro_path."/config/hostname");
	$macaddr = query($pro_path."/config/mac");
	
	set("/device/hostname", $hostname);
	set($wan1_path."/active", "1");
	set($wan2_path."/active", "0");
	set($wan1_path."/lowerlayer", "");
	set($wan1_inet_path."/addrtype", "ipv4");
	set($wan1_inet_path."/ipv4/static", "0");
	set($wan1_phyinf_path."/macaddr", $macaddr);

	clear_dns_entry($wan1_inet_path);

	echo "service WAN restart\n";
	echo "service DEVICE.HOSTNAME restart\n";

}
else if($pro_type=="STATIC")
{
	$ipaddr = query($pro_path."/config/ipaddr");
	$mask = query($pro_path."/config/mask");
	$gateway = query($pro_path."/config/gateway");
	$dns_cnt = query($pro_path."/config/dns/count");
	if($dns_cnt > 0) $primary_dns = query($pro_path."/config/dns/entry");
	if($dns_cnt > 1) $secondary_dns = query($pro_path."/config/dns/entry:2");
	$mtu = query($pro_path."/config/mtu");
	
	set($wan1_path."/active", "1");
	set($wan2_path."/active", "0");
	set($wan1_path."/lowerlayer", "");
	set($wan1_inet_path."/addrtype", "ipv4");
	set($wan1_inet_path."/ipv4/static", "1");
	anchor($wan1_inet_path."/ipv4");
	set("ipaddr", $ipaddr);
	set("mask", $mask);
	set("gateway", $gateway);
	if($mtu=="0") { set("mtu", 1500); }
	else
	{
		if($mtu >= 200 && $mtu <= 1500) { set("mtu", $mtu); }
		else { TRACE_error("mtu error!\n"); }
	}
	clear_dns_entry($wan1_inet_path);

	if($dns_cnt > 0)
	{
		set("dns/count", $dns_cnt);
		set("dns/entry", $primary_dns);
		if($dns_cnt > 1) { set("dns/entry:2", $secondary_dns); }
	}
	else { set("dns/count", 0); }
	
	echo "service WAN restart\n";

}
else if($pro_type=="PPPoE")
{
	$username = query($pro_path."/config/username");
	$password = query($pro_path."/config/password");
	$static = query($pro_path."/config/static");
	$servicename = query($pro_path."/config/servicename");
	$idletimeout = query($pro_path."/config/dialup/idletimeout");
	$mode = query($pro_path."/config/dialup/mode");
	$dns_cnt = query($pro_path."/config/dns/count");
	if($dns_cnt > 0) $primary_dns = query($pro_path."/config/dns/entry");
	if($dns_cnt > 1) $secondary_dns = query($pro_path."/config/dns/entry:2");
	$mtu = query($pro_path."/config/mtu");
	
	set($wan1_path."/active", "1");
	set($wan2_path."/active", "0");
	set($wan1_inet_path."/addrtype", "ppp4");
	set($wan1_inet_path."/ppp4/over", "eth");
	anchor($wan1_inet_path."/ppp4");
	set("static", $static);
	set("username", $username);
	set("password", $password);
	set("dialup/idletimeout", $idletimeout);
	set("pppoe/servicename", $servicename);
	set("dialup/mode", $mode);
	if($mtu=="0") { set("mtu", 1492); }
	else
	{
		if($mtu >= 200 && $mtu <= 1492) { set("mtu", $mtu); }
		else { TRACE_error("mtu error!"); }
	}
	clear_dns_entry($wan1_inet_path);
	if($dns_cnt > 0)
	{
		set("dns/count", $dns_cnt);
		set("dns/entry", $primary_dns);
		if($dns_cnt > 1) { set("dns/entry:2", $secondary_dns); }
	}
	else { set("dns/count", 0); }
	
	echo "service WAN restart\n";
}
else if($pro_type=="WISP")
{
	set($wan7_path."/active", "1");
	
	$band = query("/runtime/internetprofile/wispstatus/band");
	if($band==$WLAN1_REP)
	{
		$indx = findlist($sitesurvey_24G_e, $pro_name);
		$s_path = $sitesurvey_24G_e.":".$indx;
		$phyname = $WLAN1_REP;
	}
	else if($band==$WLAN2_REP)
	{
		$indx = findlist($sitesurvey_5G_e, $pro_name);
		$s_path = $sitesurvey_5G_e.":".$indx;
		$phyname = $WLAN2_REP;
	}
	
	set($wan7_path."/phyinf", $phyname);
	set($wan7_phyinf_path."/active", "1");
	
	$authtype = query($s_path."/authtype");
	$encrtype = query($s_path."/encrtype");
	
	if($authtype=="")
	{
		if($encrtype=="NONE")//NONE
		{
			$authtype = "OPEN";
			$encrtype = "NONE";
		}
		else if($encrtype=="WEP") //WEP
		{
			$size = query($pro_path."/config/wep/size");
			$ascii = query($pro_path."/config/wep/ascii");
			$defkey = query($pro_path."/config/wep/defkey");
			$key = query($pro_path."/config/wep/key");
			anchor($wan7_wifi_path."/nwkey/wep");
			set("size", $size);
			set("ascii", $ascii);
			set("defkey", $defkey);
			set("key", $key);
		}
	}
	else if(strstr($authtype, "WPA")!="") //WPA
	{
		if($authtype=="WPA+2PSK")
		{
			$authtype = "WPA2PSK";
		}
		
		if($encrtype=="TKIP+AES")
		{
			$encrtype = "AES";
		}
		
		$passphrase = query($pro_path."/config/psk/passphrase");
		$key = query($pro_path."/config/psk/key");
		$groupintv = query($pro_path."/config/wpa/groupintv");
		anchor($wan7_wifi_path."/nwkey/psk");
		set("passphrase", $passphrase);
		set("key", $key);
		set($wan7_wifi_path."/nwkey/wpa/groupintv", $groupintv);
	}
	set($wan7_wifi_path."/authtype", $authtype);
	set($wan7_wifi_path."/encrtype", $encrtype);
	
	set($wan1_inet_path."/addrtype", "ipv4");
	set($wan1_inet_path."/ipv4/static", "0");
	set($wan1_inet_path."/ipv4/mtu", "");
	
	echo "service WAN restart\n";
}
else if($pro_type=="USB3G")
{
	if($PROUID=="PRO-3GAUTO")
	{
		$dialno = query($pro_path."/dialno");
		$apn = query($pro_path."/apn");
		$country = query($pro_path."/country");
		$isp = query($pro_path."/isp");
		$username = query($pro_path."/username");
		$password = query($pro_path."/password");
		$authprotocol = "AUTO";
		$idt = "5";
		$dial_mode = "ondemand";
		$mtu = "1500";
		$simsts = query("/runtime/device/SIM/PINsts");
		$mcc = query($pro_path."/mcc");
		$mnc = query($pro_path."/mnc");
	}
	else
	{
		$dialno = query($pro_path."/config/dialno");
		$apn = query($pro_path."/config/apn");
		$country = query($pro_path."/config/country");
		$isp = query($pro_path."/config/isp");
		$username = query($pro_path."/config/username");
		$password = query($pro_path."/config/password");
		$authprotocol = query($pro_path."/config/authprotocol");
		$idt = query($pro_path."/config/dialup/idletimeout");
		$dial_mode = query($pro_path."/config/dialup/mode");
		$mtu = query($pro_path."/config/mtu");
		$simsts = query($pro_path."/config/simcardstatus");
		
		$operator_path = "/runtime/services/operator";
		$country_path = XNODE_getpathbytarget($operator_path, "entry", "country", $country, 0);
		$isp_path = XNODE_getpathbytarget($country_path, "entry", "profilename", $isp, 0);
		$mcc = query($isp_path."/mcc");
		$mnc = query($isp_path."/mnc");
	}
	
	set($wan3_path."/active", "1");
	set($wan3_inet_path."/ppp4/username", $username);
	set($wan3_inet_path."/ppp4/password", $password);
	set($wan3_inet_path."/ppp4/authproto", $authprotocol);
	set($wan3_inet_path."/ppp4/dialup/idletimeout", $idt);
	set($wan3_inet_path."/ppp4/dialup/mode", $dial_mode);
	set($wan3_inet_path."/ppp4/mtu", $mtu);
	set($wan3_inet_path."/ppp4/authproto", $authprotocol);
	set($wan3_inet_path."/ppp4/tty/dialno", $dialno);
	set($wan3_inet_path."/ppp4/tty/apn", $apn);
	set($wan3_inet_path."/ppp4/tty/country", $country);
	set($wan3_inet_path."/ppp4/tty/profilename", $isp);
	set($wan3_inet_path."/ppp4/tty/auto_config/mode", 0);
	set($wan3_inet_path."/ppp4/tty/simpin", $simsts);
	set($wan3_inet_path."/ppp4/tty/mcc", $mcc);
	set($wan3_inet_path."/ppp4/tty/mnc", $mnc);
	
	echo "service SIM.CHK restart\n";
}

/* IPv6 */
function getIPv6Type($pro_type)
{
	if ($pro_type == "STATIC" || $pro_type == "DHCP" || $pro_type == "WISP")
	{
		return "AUTO";
	}
	else if ($pro_type == "PPPoE" || $pro_type == "USB3G")
	{
		return "PPPDHCP";
	}
	else
	{
		TRACE_error("[activeprofile]:[getIPv6Type]:Unknown pro_type:".$pro_type);
		exit ;
	}
}
function getIpv6LLWan()
{
	foreach ("/inet/entry")
	{
		if (get("","addrtype") == "ipv6")
		{
			if (get("","ipv6/mode") == "LL")
				{ return get("","uid"); }
		}
	}
}

$ip6_ll_name = $WAN6;
$ip6_ll_path = XNODE_getpathbytarget("", "inf", "uid", $ip6_ll_name, 0);
$ip6_ll_inet_name = get("",$ip6_ll_path."/inet");
$ip6_ll_inet_path = XNODE_getpathbytarget("/inet", "entry","uid", $ip6_ll_inet_name, 0);
if ($ip6_ll_inet_path == "")
{ TRACE_error ("Failed to get ipv6 link local inet path."); return; }

set($ip6_ll_path."/active", "1");

$ip6_name = $WAN4;
$ip6_path = XNODE_getpathbytarget("", "inf", "uid", $ip6_name, 0);
$ip6_inet_name = get ("", $ip6_path."/inet");
$ip6_inet_path = XNODE_getpathbytarget("/inet", "entry","uid", $ip6_inet_name, 0);
if ($ip6_inet_path == "")
{ TRACE_error ("Failed to get ipv6 inet path."); return; }

set($ip6_path."/active", "1");

$ip6_lan = $LAN4;
$ip6_guest_lan = $LAN5;
set($ip6_inet_path."/ipv6/mode", "AUTO");
set($ip6_path."/child", $ip6_lan);
set($ip6_path."/childgz", $ip6_guest_lan);

if ($pro_type == "STATIC" || $pro_type == "DHCP" || $pro_type == "PPPoE")
{
	$phyinf = get("",$wan1_path."/phyinf");
	$ipv4_name = $WAN1;
	$ipv4_infp = $wan1_path;
	$ipv4_inetp = $wan1_inet_path;

}
else if ($pro_type == "WISP" )
{
	$phyinf = get("",$wan7_path."/phyinf");
	$ipv4_name = $WAN7;
	$ipv4_infp = $wan7_path;
	$ipv4_inetp = $wan7_inet_path;
}
else if ($pro_type == "USB3G")
{
	$phyinf = get("",$wan3_path."/phyinf");
	$ipv4_name = $WAN3;
	$ipv4_infp = $wan3_path;
	$ipv4_inetp = $wan3_inet_path;
}


set($ip6_ll_path."/phyinf",$phyinf);
set($ip6_path."/phyinf",$phyinf);


$ip6_type = getIPv6Type($pro_type);

/* Start IPv6 link-local after IPv4 linked up. */
set($ipv4_infp."/infnext", $ip6_ll_name);
set($ip6_ll_path."/infprevious",$ipv4_name);


/* Start IPv6 global after IPv6 link local linked up. */
set($ip6_ll_path."/infnext", $ip6_name);
set($ip6_path."/infprevious", $ip6_ll_name);

if ($ip6_type == "AUTO")
{
	set($ip6_ll_inet_path."/addrtype", "ipv6");

	set($ip6_path."/defaultroute", 1);
	set($ip6_inet_path."/ipv6/dhcpopt", "IA-NA+IA-PD");
}
else if ($ip6_type == "PPPDHCP")
{
	set($ip6_path."/defaultroute", 0);
	set($ip6_ll_path."/inet", $ip6_ll_inet_name);


	set($ip6_ll_inet_path."/addrtype", "ppp6");
	$PppoeUsername    = query($ipv4_inetp."/ppp4/username");
	$PppoePassword    = query($ipv4_inetp."/ppp4/password");
	$PppoeServiceName = query($ipv4_inetp."/ppp4/servicename");
 	set($ip6_ll_inet_path."/ppp6/username", $PppoeUsername);
	set($ip6_ll_inet_path."/ppp6/password", $PppoePassword);
	set($ip6_ll_inet_path."/ppp6/pppoe/servicename", $PppoeServiceName);
	if ($pro_type == "USB3G")
	{
		set($ip6_ll_inet_path."/ppp6/over", "TTY");
		set($ip6_inet_path."/ipv6/dhcpopt", "");

		/* 3G not support IPv6 yet */
		set($ip6_ll_path."/active", "0");
		set($ip6_path."/active", "0");
	}
	else
	{
		set($ip6_ll_inet_path."/ppp6/over", "eth");
		set($ip6_inet_path."/ppp6/over", "eth");
		set($ip6_inet_path."/ipv6/dhcpopt", "IA-PD");
	}
	set($ip6_ll_path."/lowerlayer", "");
	set($ip6_ll_inet_path."/ppp6/static", "0");
	del($ip6_ll_inet_path."/ppp6/ipaddr");

	set($ip6_ll_inet_path."/ppp6/dns/entry:1","");
	$MTU_v4 = query($WAN1_inetp."/ppp4/mtu");		
	set($ip6_ll_inet_path."/ppp6/mtu", $MTU_v4);	

	$dialup_v4 = query($WAN1_inetp."/ppp4/dialup/mode");
	set($ip6_ll_inet_path."/ppp6/dialup/mode", $dialup_v4);

}



/* IPv6 End */

echo "exit 0\n";

?>
