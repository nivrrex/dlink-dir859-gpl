<?
include "/htdocs/phplib/trace.php";
include "/htdocs/phplib/xnode.php";
include "/htdocs/phplib/phyinf.php";
include "/htdocs/phplib/inf.php";
include "/htdocs/phplib/mdnsresponder.php";

$active = query("/airplay/server/active");

/* info for mdnsresponder */
$mdnsname = "MDNSRESPONDER.AIRPLAY";
$port   = "5002";
$product = query("/runtime/device/modelname");
$lanmac = query("/runtime/devdata/lanmac");
if ($lanmac != "")
{
	$product = query("/runtime/device/modelname")."_".cut($lanmac, 4, ":").cut($lanmac, 5, ":");
	$macstr = cut($lanmac, 0, ":").cut($lanmac, 1, ":").cut($lanmac, 2, ":").cut($lanmac, 3, ":").cut($lanmac, 4, ":").cut($lanmac, 5, ":");
}
$srvname = $macstr."@".$product;
$srvcfg = "_raop._tcp.";
$txt = "tp=UDP\nsm=false\nsv=false\nek=1\net=0,1\ncn=0,1\nch=2\nss=16\nsr=44100\npw=false\nvn=3\ntxtvers=1";
$mdirty = 0;
$buffer = "300";

fwrite("w",$START, "#!/bin/sh\n");
fwrite("w",$STOP,  "#!/bin/sh\n");

function startcmd($cmd)	{fwrite(a,$_GLOBALS["START"], $cmd."\n");}
function stopcmd($cmd)	{fwrite(a,$_GLOBALS["STOP"], $cmd."\n");}

if ($active == 1)
{
	// setup mdns info
	$mdirty = setup_mdns_txt($mdnsname, $port, $srvname, $srvcfg, $txt);
	startcmd("shairport -a ".$product." -o ".$port." -b ".$buffer." -k ".$macstr." -d");
	stopcmd("killall shairport");
}
else
{
	// setup mdns info
	$mdirty = setup_mdns($mdnsname, 0, NULL, NULL, NULL);
}

if ($mdirty > 0)
{
	startcmd("service MDNSRESPONDER restart");
	stopcmd("sh /etc/scripts/delpathbytarget.sh /runtime/services/mdnsresponder server uid ".$mdnsname);
	stopcmd("service MDNSRESPONDER restart");
}

startcmd("exit 0");
stopcmd("exit 0");
?>
