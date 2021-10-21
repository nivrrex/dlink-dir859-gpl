#!/bin/sh
<?
// This script is used for performing DAD when WANPORT LINKUP.
include "/htdocs/webinc/config.php";
include "/htdocs/phplib/xnode.php";
include "/htdocs/phplib/trace.php";


//Get the WAN PORT devname.
$wan_ll_p = XNODE_getpathbytarget("", "inf", "uid", $WAN1, "0");
$phyinf = get("", $wan_ll_p."/phyinf");
$phyinf_p = XNODE_getpathbytarget("/runtime", "phyinf", "uid", $phyinf, "0");

$ll_devname = get ("", $phyinf_p."/name");

$ipv6_disabled = strip(fread("s","/proc/sys/net/ipv6/conf/".$ll_devname."/disable_ipv6"));

if ($ipv6_disabled == "0")	// We do not need to send DAD if IPv6 protocol stack is disabled.
{
	// Huanyao: restart the IPv6 protocol stack to sending the NS of link-local address.
	echo "echo 1 > /proc/sys/net/ipv6/conf/".$ll_devname."/disable_ipv6 \n";
	echo "echo 0 > /proc/sys/net/ipv6/conf/".$ll_devname."/disable_ipv6 \n";
}

echo "exit 0 \n";

?>
