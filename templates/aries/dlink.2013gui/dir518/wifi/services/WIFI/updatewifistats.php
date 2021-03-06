<?
include "/htdocs/phplib/trace.php";
include "/htdocs/phplib/xnode.php";
include "/htdocs/phplib/phyinf.php";
include "/etc/services/WIFI/function.php";

/********************************************************************/

if(isfile("/usr/sbin/updatewifistats")!=1)
{
	TRACE_error("/usr/sbin/updatewifistats doesn't exist \n");
	return ;	
}	

if ($PHY_UID == "") {$PHY_UID="BAND24G-1.1";}
$prefix = cut($PHY_UID, 0,"-");
$suffix = cut($PHY_UID, 1,"-");

if($suffix == "REPEATER")
{
	if($prefix == "BAND24G")
	{
		$upwifistats_pidfile = "/var/run/upwifistats24g_repeater.pid";
		$helper_script 		 = "/etc/scripts/upwifistatshlper_repeater.sh";
	}
	else
	{ 
		$upwifistats_pidfile = "/var/run/upwifistats5g_repeater.pid";
		$helper_script 		 = "/etc/scripts/upwifistatshlper_repeater.sh";
	}
	
}
else
{
	if($prefix == "BAND24G")
	{
			$upwifistats_pidfile = "/var/run/upwifistats24g.pid";
			$helper_script 		 = "/etc/scripts/upwifistatshlper_G_band.sh";
	}
	else
	{ 
		$upwifistats_pidfile = "/var/run/upwifistats5g.pid";
		$helper_script 		 = "/etc/scripts/upwifistatshlper_A_band.sh";
	}
}

$dev=devname($PHY_UID);
/* restart upwifistats 
 * 1. kill previous pid
 * 2. get the prefix, restart the upwifistats
*/

$pid = fread("", $upwifistats_pidfile);
if($pid != "") {echo "kill ".$pid."\n";}	

$upwifi_attr0 	= "updatewifistats -s ".$helper_script."  -m REALTEK -i ".$dev." ";
$upwifi_attr1 	= "-x ";	//for upwifistats argument (-x --> /phyinf:#)
$upwifi_attr2 	= "-r ";	//for upwifistats argument (-r --> /runtime/phyinf:#)

$found = 0;

if($suffix == "REPEATER")
{
	$uid = $PHY_UID;
	$p = XNODE_getpathbytarget("", "phyinf", "uid", $uid, 0);
	$r = XNODE_getpathbytarget("/runtime", "phyinf", "uid", $uid, 0);	
	
	if ($r!="") 
	{
		$upwifi_attr1 = $upwifi_attr1.$p." ";	
		$upwifi_attr2 = $upwifi_attr2.$r." ";
		$found = 1;
	} 
}
else
{
	/* for each interface. */
	$i=1;
	while ($i>0)
	{
		$uid = $prefix."-1.".$i;
		
		$p = XNODE_getpathbytarget("", "phyinf", "uid", $uid, 0);
		if ($p=="") {$i=0; break;}
	
		$r = XNODE_getpathbytarget("/runtime", "phyinf", "uid", $uid, 0);	
		if ($r!="") 
		{
			$upwifi_attr1 = $upwifi_attr1.$p." ";	
			$upwifi_attr2 = $upwifi_attr2.$r." ";
			$found = 1;
		} 
		$i++; 
	}
}

if($found==1) 
{
	$cmd = $upwifi_attr0.$upwifi_attr1.$upwifi_attr2." &";
	echo $cmd."\n";
	echo "echo $! > ".$upwifistats_pidfile."\n";
}
?>
