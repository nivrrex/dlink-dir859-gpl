<?
include "/htdocs/phplib/xnode.php";
include "/htdocs/webinc/config.php";
include "/htdocs/phplib/trace.php";

echo "#!/bin/sh\n";

function phytype($protype)
{
	if($protype=="STATIC" || $protype=="DHCP" || $protype=="PPPoE") { return "eth"; }
	else if($protype=="USB3G") { return "3g"; }
	else if($protype=="WISP") { return "wisp"; }
	
	TRACE_error("unkonw type ".$protype."\n");
}

foreach("/runtime/internetprofile/entry")
{
	$active = query("active");
	if($active==1)
	{
		$type = query("type");
		set("/runtime/internetprofile/entry:".$InDeX."/active", 0);
	}
}

$eth_p	= XNODE_getpathbytarget("", "inf", "uid", $WAN1, 0);
set($eth_p."/active", 0);
echo "service WAN-1 stop\n";
$3g_p	= XNODE_getpathbytarget("", "inf", "uid", $WAN3, 0);
set($3g_p."/active", 0);
echo "service INET.".$WAN3." stop\n"; /* need to stop 3G service. */
echo "service SIM.CHK stop\n";
$wisp_p	= XNODE_getpathbytarget("", "inf", "uid", $WAN7, 0);
set($wisp_p."/active", 0);
$wisp_phyinf = query($wisp_p."/phyinf");
$wisp_phyinf_p = XNODE_getpathbytarget("", "phyinf", "uid", $wisp_phyinf, 0);
set($wisp_phyinf_p."/active", 0);
echo "ifconfig wlan0-vxd down\n";
echo "ifconfig wlan1-vxd down\n";

/* Stop IPv6 */
$ip6_ll_p = XNODE_getpathbytarget("", "inf", "uid", $WAN6, 0);
set ($ip6_ll_p."/active", 0);
echo "service INET.".$WAN6." stop\n";

$ip6_p = XNODE_getpathbytarget("", "inf", "uid", $WAN4, 0);
set ($ip6_p."/active", 0);
echo "service INET.".$WAN4." stop\n";


echo "exit 0\n";

?>
