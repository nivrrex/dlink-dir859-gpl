<?
include "/htdocs/phplib/trace.php";
include "/htdocs/phplib/xnode.php";
include "/etc/services/INET/interface.php";

fwrite("w",$START, "#!/bin/sh\n");
fwrite("a", $STOP, "#!/bin/sh\n");

if (query("/runtime/device/layout")=="bridge")
{
	/* Start all LAN interfaces. */
	ifinetsetupall("BRIDGE");
	fwrite("a",$START, "service HTTP restart\n");
}
else
{
	SHELL_info($START, "BRIDGE: The device is not in the bridge mode.");
	SHELL_info($STOP,  "BRIDGE: The device is not in the bridge mode.");
}

/* Done */
fwrite("a",$START, "exit 0\n");
fwrite("a", $STOP, "exit 0\n");
?>
