<?
include "/htdocs/phplib/xnode.php";

fwrite("w",$START, "#!/bin/sh\n");
fwrite("a",$START, "service IPTMACCTRL restart\n");
fwrite("a",$START, "service IP6TMACCTRL restart\n");
fwrite("w",$STOP,  "#!/bin/sh\n");
fwrite("a",$STOP, "service IPTMACCTRL stop\n");
fwrite("a",$STOP, "service IP6TMACCTRL stop\n");

/* refresh the chain of LAN interfaces */
$i = 1;
while ($i>0)
{
	$ifname = "LAN-".$i;
	$ifpath = XNODE_getpathbytarget("", "inf", "uid", $ifname, 0);
	if ($ifpath == "") { $i = 0; break; }
	fwrite("a",$_GLOBALS["START"], "service IPT.".$ifname." restart\n");
	fwrite("a",$_GLOBALS["STOP"],  "service IPT.".$ifname." restart\n");
	fwrite("a",$_GLOBALS["START"], "service IP6T.".$ifname." restart\n");
	fwrite("a",$_GLOBALS["STOP"],  "service IP6T.".$ifname." restart\n");
	$i++;
}
?>
