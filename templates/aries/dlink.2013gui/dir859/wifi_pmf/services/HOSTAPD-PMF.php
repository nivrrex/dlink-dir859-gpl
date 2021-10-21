<?
include "/htdocs/phplib/xnode.php";
include "/htdocs/phplib/trace.php";
include "/htdocs/phplib/phyinf.php";
include "/htdocs/webinc/config.php";
include "/var/topology.conf";

function startcmd($cmd)	{fwrite(a,$_GLOBALS["START"], $cmd."\n");}
function stopcmd($cmd)	{fwrite(a,$_GLOBALS["STOP"], $cmd."\n");}

$pmf =1;

fwrite(w,$_GLOBALS["START"], "#!/bin/sh\n");
fwrite(w,$_GLOBALS["STOP"],  "#!/bin/sh\n");
startcmd("killall hostapd > /dev/null 2>&1");
stopcmd("killall hostapd > /dev/null 2>&1; sleep 1");

//cfg is in /var/topology.conf
startcmd("hostapd -B ".$cfg."-e /var/run/123 &");

?>
