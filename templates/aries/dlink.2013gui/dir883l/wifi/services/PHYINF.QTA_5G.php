<?
include "/htdocs/phplib/xnode.php";
include "/htdocs/phplib/trace.php";
include "/htdocs/phplib/phyinf.php";
include "/htdocs/webinc/config.php";

function startcmd($cmd) {fwrite(a,$_GLOBALS["START"], $cmd."\n");}
function stopcmd($cmd)  {fwrite(a,$_GLOBALS["STOP"], $cmd."\n");}
function error($err)    {startcmd("exit ".$err); stopcmd("exit ".$err); return $err;}

fwrite("w",$START, "#!/bin/sh\n");
fwrite("w", $STOP, "#!/bin/sh\n");
$p        = XNODE_getpathbytarget("", "phyinf", "uid", "BAND5G-1.1", 0);
if ($p=="") return error(9);
if (query($p."/active")!=1) return error(8);

$wifi     = XNODE_getpathbytarget("/wifi", "entry", "uid", query($p."/wifi"), 0);
$wlmode   = query($p."/media/wlmode");
$channel  = query($p."/media/channel");
$bandwidth= query($p."/media/dot11n/bandwidth");
$ssid     = query($wifi."/ssid");
$auth     = query($wifi."/authtype");
$encr     = "TKIPandAESEncryption";
$pwd      = query($wifi."/nwkey/psk/key");

$bw1= cut($bandwidth, 1, "+");
$bw2= cut($bandwidth, 2, "+");

if($auth == "WPA+2PSK")
	$authtype = "WPAand11i";
else
	$authtype = "Basic";

if($authtype == "Basic")
	startcmd("/etc/scripts/basic_5G.sh ".$ssid." ".$authtype."");
else
	startcmd("/etc/scripts/basic_5G.sh ".$ssid." ".$authtype." ".$encr." ".$pwd."");

if($wlmode == "acna")
	startcmd("qcsapi_pcie set_vht wifi0 1");
else
	startcmd("qcsapi_pcie set_vht wifi0 0");

	startcmd("qcsapi_pcie set_channel wifi0 ".$channel."");

if($bw2 != "")
	startcmd("qcsapi_pcie set_bw wifi0 ".$bw2."");
else
	startcmd("qcsapi_pcie set_bw wifi0 ".$bw1."");


stopcmd("echo PHYINF.QTA_5G stop");
stopcmd("qcsapi_pcie rfenable wifi0 0");

fwrite("a",$START,	"exit 0\n");
fwrite("a", $STOP,	"exit 0\n");
?>
