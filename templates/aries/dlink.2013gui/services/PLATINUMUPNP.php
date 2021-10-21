<?
include "/htdocs/phplib/trace.php";
include "/htdocs/phplib/xnode.php";

$renderer_active = query("/platinumupnp/renderer/active");

fwrite("w",$START, "#!/bin/sh\n");
fwrite("w",$STOP,  "#!/bin/sh\n");

function startcmd($cmd)	{fwrite(a,$_GLOBALS["START"], $cmd."\n");}
function stopcmd($cmd)	{fwrite(a,$_GLOBALS["STOP"], $cmd."\n");}

if ($renderer_active == 1)
{
	startcmd("killall mpg123");
	startcmd("MicroMediaRenderer &");
	stopcmd("killall MicroMediaRenderer");
	stopcmd("killall mpg123");
	startcmd("exit 0");
	stopcmd("exit 0");
}
else
{
	startcmd("exit 9");
	stopcmd("exit 9");
}
?>
