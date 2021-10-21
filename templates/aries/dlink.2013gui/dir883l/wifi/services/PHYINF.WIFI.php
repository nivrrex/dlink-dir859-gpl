<?
include "/htdocs/phplib/xnode.php";
include "/etc/services/PHYINF/phywifi.php";

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

fwrite("a",$START,"service PHYINF.BAND24G ".schcmd("BAND24G-1.1")."\n");
fwrite("a",$START,"service PHYINF.QTA_5G ".schcmd("BAND5G-1.1")."\n");

fwrite("a",$STOP,"service PHYINF.QTA_5G stop\n");
fwrite("a",$STOP,"service PHYINF.BAND24G stop\n");

fwrite("a",$START,	"exit 0\n");
fwrite("a",$STOP,	"exit 0\n");
?>
