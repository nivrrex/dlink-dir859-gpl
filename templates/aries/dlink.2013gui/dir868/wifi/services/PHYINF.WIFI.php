<?
include "/htdocs/phplib/xnode.php";
include "/htdocs/webinc/config.php";

function schcmd($uid)
{
	/* Get schedule setting */
	$base = XNODE_getpathbytarget("", "phyinf", "uid", $uid, 0);
	$sch = query($base."/schedule");
	if ($sch=="")	{$cmd = "start";}
	else	{$cmd = XNODE_getschedule2013cmd($sch);}
	return $cmd;
}

/********************************************************************/
fwrite("w",$START, "#!/bin/sh\n");
fwrite("w", $STOP, "#!/bin/sh\n");

if(query("/device/layout")=="router")
{
	fwrite("a",$START,	
		"service PHYINF.BAND24G ".schcmd("BAND24G-1.1")."\n".
		"service PHYINF.BAND5G ".schcmd("BAND5G-1.1")."\n"
		);
	
	fwrite("a",$STOP,
		"service PHYINF.BAND24G stop\n".
		"service PHYINF.BAND5G stop\n"
		);
}
else if(query("/device/layout")=="bridge")
{
	fwrite("a",$START,"service PHYINF.WIFI-REPEATER ".schcmd("WIFI-REPEATER")."\n");	
	fwrite("a",$STOP,"service PHYINF.WIFI-REPEATER stop\n");	
}

fwrite("a",$START,	"exit 0\n");
fwrite("a",$STOP,	"exit 0\n");
?>
