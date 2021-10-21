#!/bin/sh
<?
include "/htdocs/phplib/xnode.php";

$inf_base = XNODE_getpathbytarget("/runtime", "inf", "uid", $INF, 0);
$static = query($inf_base."/inet/ipv4/static");
if($static == "1")
	echo "service DHCPS4.".$INF." stop\n";

$wl_start = get("", "/runtime/device/uptime");
set($inf_base."/inet/uptime", $wl_start);
	
echo "exit 0\n";
?>
