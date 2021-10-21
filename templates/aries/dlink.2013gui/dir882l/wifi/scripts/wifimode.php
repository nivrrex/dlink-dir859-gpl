<?
include "/htdocs/phplib/xnode.php";

$UID24G		= "BAND24G-1.1";
$UID5G		= "BAND5G-1.1";
$UIDSTA		= "WIFI-STA";
$UIDSTA5G	= "WIFI-STA5G";

$wlan24	= XNODE_getpathbytarget("", "phyinf", "uid", $UID24G, 0);
$wlan5	= XNODE_getpathbytarget("", "phyinf", "uid", $UID5G, 0);
$wlan24_sta	= XNODE_getpathbytarget("", "phyinf", "uid", $UIDSTA, 0);
$wlan5_sta	= XNODE_getpathbytarget("", "phyinf", "uid", $UIDSTA5G, 0);

$wlan24_active = query($wlan24."/active");
$wlan5_active = query($wlan5."/active");
$wlan24_sta_active = query($wlan24_sta."/active");
$wlan5_sta_active = query($wlan5_sta."/active");

if ($wlan24_active == "1" || $wlan5_active == "1"){
	if ( $wlan24_sta_active == "1" || $wlan5_sta_active == "1")
		set("/runtime/device/switchmode","REPEATER");
	else
		set("/runtime/device/switchmode","AP");
}
else{
	if ( $wlan24_sta_active == "1" || $wlan5_sta_active == "1")
		set("/runtime/device/switchmode","STA");
	else
		set("/runtime/device/switchmode","AP");
}
?>
