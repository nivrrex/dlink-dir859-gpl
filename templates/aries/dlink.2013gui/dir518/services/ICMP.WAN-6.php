<? /* vi: set sw=4 ts=4: */
fwrite("w",$START, "#!/bin/sh\n");
fwrite("w", $STOP, "#!/bin/sh\n");

fwrite("a",$START, "service IPT.WAN-6 restart\n");
?>
