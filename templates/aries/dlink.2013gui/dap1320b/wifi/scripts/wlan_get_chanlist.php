<?
include "/htdocs/phplib/xnode.php";
include "/etc/services/PHYINF/phywifi.php";
echo "#!/bin/sh\n";

$UID24G	= "BAND24G-1.1";
$UID5G	= "BAND5G-1.1";
$dev_24	= devname($UID24G);
$dev_5	= devname($UID5G);
TRACE_info($UID24G);
TRACE_info($dev_24);
setattr("/runtime/get_channel_24",	"get","iwpriv ".$dev_24." channels | grep List | cut -f2 -d: | sed  -e 's/[ ^I]/,/g' | sed 's/,$//g' | sed 's/^,//g'");
setattr("/runtime/get_channel_5",	"get","iwpriv ".$dev_5." channels | grep List | cut -f2 -d: | sed  -e 's/[ ^I]/,/g' | sed 's/,$//g' | sed 's/^,//g'");

echo "exit 0\n";
?>
