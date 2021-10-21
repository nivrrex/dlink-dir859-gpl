#!/bin/sh
<?
include "/htdocs/phplib/trace.php";
include "/htdocs/phplib/xnode.php";
include "/etc/services/WIFI/function.php";

/************************************************************************/
$wpsfile = "/var/wps_start_pbc";
$wispfile = "/var/wisp_detect";
$UIDSTA = "BAND24G-REPEATER";
$UIDSTA5G = "BAND5G-REPEATER";

if ($ACTION == "DEL")
{
	if(isfile($wpsfile)==0)
	{
		echo 'usockc /var/wanmonitor_ctrl WISP_PHYDOWN\n';
	}
}
else if($ACTION == "ADD")
{
	if(isfile($wpsfile)==0)
	{
		echo 'usockc /var/wanmonitor_ctrl WISP_PHYUP\n';
		set("/runtime/internetprofile/wispstatus/status", "LINKUP");
	}
}
else if($ACTION == "WISP_DETECT" )
{
	$DEV=devname($uid);

	$dev24=devname($UIDSTA);
	$dev5=devname($UIDSTA5G);
	echo 'ifconfig '.$dev24.' down\n';
	echo 'ifconfig '.$dev5.' down\n';
	
	wisp_profile($DEV,"script",$ssid,$authtype,$encrtype,$psk,$wep_key,$wep_key_len,$ascii);
	echo 'ifconfig '.$DEV.' up\n';
}
?>
