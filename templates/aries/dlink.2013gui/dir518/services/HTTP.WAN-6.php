<?
include "/etc/services/HTTP/httpsvcs.php";
fwrite("w",$START,"#!/bin/sh\n");
fwrite("w", $STOP,"#!/bin/sh\n");
fwrite("a",$START,"service IPT.WAN-6 restart\n");
httpsetup("WAN-6");
?>
