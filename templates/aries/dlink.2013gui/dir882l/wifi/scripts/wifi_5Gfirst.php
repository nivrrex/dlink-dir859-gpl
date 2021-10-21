#!/bin/sh
<?
include "/htdocs/phplib/xnode.php";

$UID24G		= "BAND24G-1.1";
$UID5G		= "BAND5G-1.1";
$UIDSTA		= "WIFI-STA";
$UIDSTA5G	= "WIFI-STA5G";

$phyp24	= XNODE_getpathbytarget("", "phyinf", "uid", $UIDSTA, 0);
$wifi24 = XNODE_getpathbytarget("/wifi", "entry", "uid", query($phyp24."/wifi"), 0);
$rphyp24= XNODE_getpathbytarget("/runtime", "phyinf", "uid", $UIDSTA, 0);

$phyp5	= XNODE_getpathbytarget("", "phyinf", "uid", $UIDSTA5G, 0);
$wifi5	= XNODE_getpathbytarget("/wifi", "entry", "uid", query($phyp5."/wifi"), 0);
$rphyp5	= XNODE_getpathbytarget("/runtime", "phyinf", "uid", $UIDSTA5G, 0);


$dev24	= query($rphyp24."/name");
$dev5	= query($rphyp5."/name");

if (query($phyp5."/active")!="1" && query($phyp24."/active")!="1")
{
	echo 'exit 0\n';
}
else if (query($phyp5."/active")=="1" && query($phyp24."/active")=="1"){
	$pidfile = "/var/run/wifi_5Gfirst.pid";
	$pid = fread("s", $pidfile);
	if ($pid != "")
	{
		echo "kill ".$pid."\n";
		echo "rm ".$pidfile."\n";
	}
	echo 'iwpriv '.$dev5.' set_mib func_off=1\n';
	echo 'iwpriv '.$dev24.' set_mib func_off=1\n';
	if ($wifi5 != "")
		echo 'wifi_5Gfirst -a '.$dev5.' -g '.$dev24.' -w '.$wifi5.' -p /var/run/wifi_5Gfirst.pid &\n';
	else
		echo 'wifi_5Gfirst -a '.$dev5.' -g '.$dev24.' -w '.$wifi24.' -p /var/run/wifi_5Gfirst.pid &\n';
}
else if (query($phyp5."/active")=="1"){
	if(query($wifi5."/opmode") == "REPEATER"){
		echo 'while [ "`ifconfig wlan0 | grep "RUNNING"`" == "" ]; do\n';
		echo 'echo sleep 1, wait for wlan0 up > /dev/console\n';
		echo 'sleep 1\n';
		echo 'done\n';
	}
	echo 'sleep 1\n';
	echo 'iwpriv '.$dev5.' set_mib func_off=0\n';
	echo 'ifconfig '.$dev5.' up\n';
	echo 'echo 1 > /var/run/'.$dev5.'.UP\n';
}
else if (query($phyp24."/active")=="1"){
	if(query($wifi24."/opmode") == "REPEATER"){
		echo 'while [ "`ifconfig wlan1 | grep "RUNNING"`" == "" ]; do\n';
		echo 'echo sleep 1, wait for wlan1 up > /dev/console\n';
		echo 'sleep 1\n';
		echo 'done\n';
	}
	echo 'sleep 1\n';
	echo 'iwpriv '.$dev24.' set_mib func_off=0\n';
	echo 'ifconfig '.$dev24.' up\n';
	echo 'echo 1 > /var/run/'.$dev24.'.UP\n';
}

?>
