<?
include "/htdocs/phplib/trace.php";
include "/htdocs/phplib/xnode.php";

fwrite("w",$START,	"#!/bin/sh\n");
fwrite("w",$STOP,	"#!/bin/sh\n");
fwrite("a",$START,	"sh /etc/events/sim_chk.sh\n");
fwrite("a",$START,	"exit 0\n");
fwrite("a",$STOP,	"exit 0\n");
?>
