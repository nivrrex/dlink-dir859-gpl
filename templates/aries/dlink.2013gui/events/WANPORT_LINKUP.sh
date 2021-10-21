<?
include "/htdocs/webinc/config.php";
include "/htdocs/phplib/xnode.php";
include "/htdocs/phplib/phyinf.php";

echo '#!/bin/sh\n';
$path_wan1 = XNODE_getpathbytarget("", "inf", "uid", $WAN1, 0);
$wan1_phyuid  = get("x", $path_wan1."/phyinf");
$runtime_wan1_phy  =  XNODE_getpathbytarget("/runtime", "phyinf", "uid", $wan1_phyuid, 0);
set($runtime_wan1_phy."/linkuptime", get("", "/runtime/device/uptime"));
echo 'exit 0\n';
?>
