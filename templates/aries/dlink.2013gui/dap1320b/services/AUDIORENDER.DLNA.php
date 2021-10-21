<?
include "/htdocs/phplib/trace.php";

$active = query("/device/audiorender/dlna");
$product = query("/device/audiorender/medianame");

fwrite("w",$START, "#!/bin/sh\n");
fwrite("w",$STOP,  "#!/bin/sh\n");

function startcmd($cmd)	{fwrite(a,$_GLOBALS["START"], $cmd."\n");}
function stopcmd($cmd)	{fwrite(a,$_GLOBALS["STOP"], $cmd."\n");}

if ($active == "1")
{
	startcmd("MicroMediaRenderer -f ".$product." &");
	stopcmd("killall MicroMediaRenderer");
}

startcmd("exit 0");
stopcmd("exit 0");
?>
