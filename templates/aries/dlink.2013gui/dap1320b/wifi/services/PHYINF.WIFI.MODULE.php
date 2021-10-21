<?
include "/htdocs/phplib/xnode.php";
include "/htdocs/phplib/trace.php";


/********************************************************************/
fwrite("w",$START, "#!/bin/sh\n");
fwrite("w", $STOP, "#!/bin/sh\n");

fwrite("a",$START,"insmod /lib/modules/rt2860v2_ap.ko\n".);
fwrite("a",$STOP,"rmmod rt2860v2_ap.ko\n".);

fwrite("a",$START,  "exit 0\n");
fwrite("a",$STOP,   "exit 0\n");
?>

