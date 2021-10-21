<?
include "/htdocs/phplib/xnode.php";
include "/etc/services/PHYINF/phywifi.php";

fwrite("w",$START, "#!/bin/sh\n");
fwrite("w", $STOP, "#!/bin/sh\n");

fwrite("a",$START,"service WIFI_MODS start\n");
$wifi_activateVAP = get_vap_activate_file_path();
if(isfile($wifi_activateVAP) == 1) { unlink($wifi_activateVAP); }
fwrite("a",$START,"service PHYINF.BAND24G-1.1 start\n");
fwrite("a",$START,"service PHYINF.BAND24G-1.2 start\n");
fwrite("a",$START,"service WIFI_ACTIVATE start\n");

fwrite("a",$STOP,"killall updatewifistats\n");
fwrite("a",$STOP,"service WIFI_ACTIVATE stop\n");
fwrite("a",$STOP,"service PHYINF.BAND24G-1.2 stop\n");
fwrite("a",$STOP,"service PHYINF.BAND24G-1.1 stop\n");
fwrite("a",$STOP,"service WIFI_MODS stop\n");

fwrite("a",$START,	"exit 0\n");
fwrite("a", $STOP,	"exit 0\n");
?>
