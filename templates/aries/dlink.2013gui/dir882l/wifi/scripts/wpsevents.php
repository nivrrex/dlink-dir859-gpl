<?
include "/htdocs/phplib/xnode.php";

echo "#!/bin/sh\n";

$uid1 = "BAND24G-1.1";
$uid2 = "BAND5G-1.1";
$uid3 = "WIFI-STA";
$uid4 = "WIFI-STA5G";

$p1 = XNODE_getpathbytarget("", "phyinf", "uid", $uid1, 0);
$p2 = XNODE_getpathbytarget("", "phyinf", "uid", $uid2, 0);
$p3 = XNODE_getpathbytarget("", "phyinf", "uid", $uid3, 0);
$p4 = XNODE_getpathbytarget("", "phyinf", "uid", $uid4, 0);

if ($p1==""||$p2=="") echo "exit 0\n";
if ($p3==""&&$p4=="") echo "exit 0\n";

$wifi1 = XNODE_getpathbytarget("/wifi", "entry", "uid", query($p1."/wifi"),0);
$wifi2 = XNODE_getpathbytarget("/wifi", "entry", "uid", query($p2."/wifi"),0);
$wifi3 = XNODE_getpathbytarget("/wifi", "entry", "uid", query($p3."/wifi"),0);
$wifi4 = XNODE_getpathbytarget("/wifi", "entry", "uid", query($p4."/wifi"),0);

$wps = 0;
$wps_sta=0;
if (query($p1."/active")==1 && query($wifi1."/wps/enable")==1) $wps++;
if (query($p2."/active")==1 && query($wifi2."/wps/enable")==1) $wps++;
if (query($p3."/active")==1 && query($wifi3."/wps/enable")==1) $wps_sta++;
if (query($p4."/active")==1 && query($wifi4."/wps/enable")==1) $wps_sta++;

if ($ACTION == "ADD")
{
	/* Someone uses wps, so add the events for WPS. */
	if ($wps > 0)
	{
		//AP only
		//echo 'event DHCP.IP.CHANGE insert "'.$uid1.':service PHYINF.WIFI restart"\n';
		echo 'event WPSPIN add "/etc/scripts/wps.sh pin AP"\n';
		echo 'event WPSPBC.PUSH add "/etc/scripts/wps.sh pbc AP"\n';
		echo 'event WPS.STOP add "/etc/scripts/wps.sh stop AP"\n';
	}
	if ($wps_sta > 0)
	{
		$opmode = query($wifi3."/opmode");
		if ($opmode == "") $opmode = query($wifi4."/opmode");

		if ($wps > 0){
			echo 'event WPSPIN insert "/etc/scripts/wps.sh pin '.$opmode.'"\n';
			echo 'event WPSPBC.PUSH insert "/etc/scripts/wps.sh pbc '.$opmode.'"\n';
		}
		else{
			echo 'event WPSPIN add "/etc/scripts/wps.sh pin '.$opmode.'"\n';
			echo 'event WPSPBC.PUSH add "/etc/scripts/wps.sh pbc '.$opmode.'"\n';
		}
		echo 'event WPS_STA.STOP add "/etc/scripts/wps.sh stop '.$opmode.'"\n';
	}
	if ($wps > 0 && $wps_sta > 0)
	{
		echo 'event WPSPBC.PUSH insert "wps_monitor -a /runtime/wps/state -s /runtime/wps_sta/state &"\n';
	}
}
else if ($ACTION == "FLUSH")
{
	/* No body uses wps, so we can flush it. */
	if ($wps == 0)
	{
		echo "event WPSPIN flush\n";
		echo "event WPSPBC.PUSH flush\n";
	}
}

echo "exit 0\n";

?>
