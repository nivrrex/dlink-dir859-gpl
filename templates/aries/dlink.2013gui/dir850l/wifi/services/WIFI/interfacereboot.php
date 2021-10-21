<?
include "/htdocs/phplib/xnode.php";

function isscheduled($uid)
{
	$p = XNODE_getpathbytarget("", "phyinf", "uid", $uid, 0);
	$sch = XNODE_getschedule($p);
	return $sch;
}
function schcmd($uid)
{
	/* Get schedule setting */
	$sch = isscheduled($uid);
	$schid =  query($p."/schedule");
	if ($sch=="") $cmd = "start";
	else	{$cmd = XNODE_getschedule2013cmd($schid);}
	return $cmd;
}

if ($ACTION=="restart_guest")
{
	echo "#!/bin/sh\n";
	if($UID=="BAND24G-1.1" && isscheduled($UID)!="" && isfile("/var/run/".$UID.".UP")==1)
	{
		if(isfile("/var/run/BAND24G-1.2.sch")==1)
		{
			echo "service PHYINF.BAND24G-1.2 stop\n";
			echo "service PHYINF.BAND24G-1.2 ".schcmd("BAND24G-1.2")."\n";
		}
	}
	if($UID=="BAND5G-1.1"&& isscheduled($UID)!="" && isfile("/var/run/".$UID.".UP")==1)
	{
		if(isfile("/var/run/BAND5G-1.2.sch")==1)
		{
			echo "service PHYINF.BAND5G-1.2 stop\n";
			echo "service PHYINF.BAND5G-1.2 ".schcmd("BAND5G-1.2")."\n";
		}
	}
	echo "exit 0\n";
}
else
{
	echo "#!/bin/sh\n";
	if($UID=="BAND24G-1.1" && isscheduled($UID)!="" && isfile("/var/run/".$UID.".DOWN")==1)
	{
		if(isfile("/var/run/BAND5G-1.1.UP")==1)
		{
			echo "service PHYINF.BAND5G-1.1 stop\n";
			echo "service PHYINF.BAND5G-1.1 ".schcmd("BAND5G-1.1")."\n";
		}
		if(isfile("/var/run/BAND5G-1.2.UP")==1)
		{
			echo "service PHYINF.BAND5G-1.2 stop\n";
			echo "service PHYINF.BAND5G-1.2 ".schcmd("BAND5G-1.2")."\n";
		}
		if(isfile("/var/run/BAND24G-1.2.UP")==1)
		{
			echo "service PHYINF.BAND24G-1.2 stop\n";
			echo "service PHYINF.BAND24G-1.2 ".schcmd("BAND24G-1.2")."\n";
		}
	}
	if($UID=="BAND24G-1.2"&& isscheduled($UID)!="" && isfile("/var/run/".$UID.".DOWN")==1)
	{
		if(isfile("/var/run/BAND24G-1.1.UP")==1)
		{
			echo "service PHYINF.BAND24G-1.1 stop\n";
			echo "service PHYINF.BAND24G-1.1 ".schcmd("BAND24G-1.1")."\n";
		}
		if(isfile("/var/run/BAND5G-1.1.UP")==1)
		{
			echo "service PHYINF.BAND5G-1.1 stop\n";
			echo "service PHYINF.BAND5G-1.1 ".schcmd("BAND5G-1.1")."\n";
		}
		if(isfile("/var/run/BAND5G-1.2.UP")==1)
		{
			echo "service PHYINF.BAND5G-1.2 stop\n";
			echo "service PHYINF.BAND5G-1.2 ".schcmd("BAND5G-1.2")."\n";
		}
	}
	if($UID=="BAND5G-1.1"&& isscheduled($UID)!="" && isfile("/var/run/".$UID.".DOWN")==1)
	{
		if(isfile("/var/run/BAND5G-1.2.UP")==1)
		{
			echo "service PHYINF.BAND5G-1.2 stop\n";
			echo "service PHYINF.BAND5G-1.2 ".schcmd("BAND5G-1.2")."\n";
		}
		if(isfile("/var/run/BAND24G-1.1.UP")==1)
		{
			echo "service PHYINF.BAND24G-1.1 stop\n";
			echo "service PHYINF.BAND24G-1.1 ".schcmd("BAND24G-1.1")."\n";
		}
		if(isfile("/var/run/BAND24G-1.2.UP")==1)
		{
			echo "service PHYINF.BAND24G-1.2 stop\n";
			echo "service PHYINF.BAND24G-1.2 ".schcmd("BAND24G-1.2")."\n";
		}
	}
	if($UID=="BAND5G-1.2"&& isscheduled($UID)!="" && isfile("/var/run/".$UID.".DOWN")==1)
	{
		if(isfile("/var/run/BAND5G-1.1.UP")==1)
		{
			echo "service PHYINF.BAND5G-1.1 stop\n";
			echo "service PHYINF.BAND5G-1.1 ".schcmd("BAND5G-1.1")."\n";
		}
		if(isfile("/var/run/BAND24G-1.1.UP")==1)
		{
			echo "service PHYINF.BAND24G-1.1 stop\n";
			echo "service PHYINF.BAND24G-1.1 ".schcmd("BAND24G-1.1")."\n";
		}
		if(isfile("/var/run/BAND24G-1.2.UP")==1)
		{
			echo "service PHYINF.BAND24G-1.2 stop\n";
			echo "service PHYINF.BAND24G-1.2 ".schcmd("BAND24G-1.2")."\n";
		}
	}
	echo "exit 0\n";
}
?>
