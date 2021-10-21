#!/bin/sh
<?
include "/htdocs/phplib/trace.php";
include "/htdocs/phplib/xnode.php";

function cmd($cmd) {echo $cmd."\n";}
function msg($msg) {cmd("echo ".$msg." > /dev/console");}

/*****************************************/
function add_each($list, $path, $node)
{
	//echo "# add_each(".$list.",".$path.",".$node.")\n";
	$i = 0;
	$cnt = scut_count($list, "");
	while ($i < $cnt)
	{
		$val = scut($list, $i, "");
		if ($val!="") add($path."/".$node, $val);
		$i++;
	}
	return $cnt;
}

/*****************************************/

function dev_detach($hasevent)
{
	$sts = XNODE_getpathbytarget("/runtime", "inf", "uid", $_GLOBALS["INF"], 0);
	if ($sts=="") return $_GLOBALS["INF"]." has no runtime nodes.";
	if (query($sts."/inet/addrtype")!="ipv6") return $_GLOBALS["INF"]." is not ipv6.";
	if (query($sts."/inet/ipv6/valid")!=1) return $_GLOBALS["INF"]." is not active.";
	$devnam = query($sts."/devnam");
	if ($devnam=="") return $_GLOBALS["INF"]." has no device name.";

	anchor($sts."/inet/ipv6");
	$mode	= query("mode");
	$ipaddr	= query("ipaddr");
	$prefix	= query("prefix");
	$gw	= query("gateway");
	$dhcpopt= query("dhcpopt");
	$defrt	= query($sts."/defaultroute");
	$blackholepfx = query($sts."/blackhole/prefix");

	/* default route */
	if ($defrt!="" && $defrt>0)
	{
		if ($gw!="")			cmd("ip -6 route del ::/0 via ".$gw." dev ".$devnam);
		else if ($mode!="TSP")	cmd("ip -6 route del ::/0 dev ".$devnam);
	}
	else cmd("ip -6 route flush table ".$_GLOBALS["INF"]);

	/* TSPC will destroy the tunnel, so we don't need to detach. */
	if ($mode != "TSP")
	{
		/* peer-to-peer */
		if ($prefix==128 && $gw!="") cmd("ip -6 route del ".$gw."/128 dev ".$devnam);

		/* detach */
		if ($mode=="LL") msg($_GLOBALS["INF"].' a is link local interface.');
		else if	($mode=="STATEFUL" && $dhcpopt!="IA-PD") msg($_GLOBALS["INF"].' is a stateful-IANA interface.');
		else
		{
			$netid = ipv6networkid($ipaddr, $prefix);
			cmd("ip -6 route del ".$netid."/".$prefix." dev ".$devnam);
			cmd("ip -6 addr del ".$ipaddr."/".$prefix." dev ".$devnam);
		}
	}

	/* remove blackhole rule if needed */
	if ($blackholepfx != "")
	{
		$blackholeplen = query($sts."/blackhole/plen");
		cmd("ip -6 route del blackhole ".$blackholepfx."/".$blackholeplen." dev lo");
		del($sts."/blackhole");
	}

	if ($hasevent>0)
	{
		cmd("rm -f /var/run/".$_GLOBALS["INF"].".UP");
		cmd("event ".$_GLOBALS["INF"].".DOWN");
	}

	del($sts."/inet");
	del($sts."/devnam");
}

function dev_attach($hasevent)
{
	$cfg = XNODE_getpathbytarget("", "inf", "uid", $_GLOBALS["INF"], 0);
	if ($cfg=="") return $_GLOBALS["INF"]." does not exist!";

	/* The runtime node of INF should already be created when starting INET service.
	 * Set the create flag to make sure is will always be created. */
	$sts = XNODE_getpathbytarget("/runtime", "inf", "uid", $_GLOBALS["INF"], 1);

	/* Just in case the device is still alive. */
	if (query($sts."/inet/ipv6/valid")==1) dev_detach(0);

	/* Get the default metric from config. */
	$defrt = query($cfg."/defaultroute");
	
	/***********************************************/
	/* Update Status */
	$M = $_GLOBALS["MODE"];
	anchor($sts);
	set("defaultroute", 	$defrt);
	set("devnam",			$_GLOBALS["DEVNAM"]);
	set("inet/uid",			query($cfg."/inet"));
	set("inet/addrtype",	"ipv6");
	set("inet/uptime",		query("/runtime/device/uptime"));
	set("inet/ipv6/valid",	"1");
	/* INET */
	anchor($sts."/inet/ipv6");
	set("mode",		$M);
	set("ipaddr",	$_GLOBALS["IPADDR"]);
	set("prefix",	$_GLOBALS["PREFIX"]);
	set("gateway",	$_GLOBALS["GATEWAY"]);
	set("routerlft",$_GLOBALS["ROUTERLFT"]);
	set("preferlft",$_GLOBALS["PREFIXLFT"]);
	set("validlft",	$_GLOBALS["VALIDLFT"]);
	/* DNS & ROUTING */
	add_each($_GLOBALS["DNS"], $sts."/inet/ipv6", "dns");
	/***********************************************/
	/* attach */
	$dhcpopt = query("dhcpopt");
	if		($M=="LL") msg($_GLOBALS["INF"].' a is link local interface.');
	else if	($M=="STATELESS") msg($_GLOBALS["INF"].' is a self-configured interface.');
	else if	($M=="STATEFUL" && $dhcpopt!="IA-PD") msg($_GLOBALS["INF"].' is a stateful-IANA interface.');
	else cmd("ip -6 addr add ".$_GLOBALS["IPADDR"]."/".$_GLOBALS["PREFIX"]." dev ".$_GLOBALS["DEVNAM"]);

	/* Handle the peer-to-peer connection. */
	if ($_GLOBALS["PREFIX"]==128 && $_GLOBALS["GATEWAY"]!="")
		cmd("ip -6 route add ".$_GLOBALS["GATEWAY"]."/128 dev ".$_GLOBALS["DEVNAM"]);
	/* gateway */
	if ($defrt!="" && $defrt>0)
	{
		if ($_GLOBALS["GATEWAY"]!="")
			cmd("ip -6 route add ::/0 via ".$_GLOBALS["GATEWAY"]." dev ".$_GLOBALS["DEVNAM"]." metric ".$defrt);
		else
			cmd("ip -6 route add ::/0 dev ".$_GLOBALS["DEVNAM"]." metric ".$defrt);
	}
	else
	{
		$netid = ipv6networkid($_GLOBALS["IPADDR"], $_GLOBALS["PREFIX"]);
		cmd("ip -6 route add ".$netid."/".$_GLOBALS["PREFIX"]." dev ".$_GLOBALS["DEVNAM"].
				" src ".$_GLOBALS["IPADDR"]." table ".$_GLOBALS["INF"]);
	}
	/* Routing */
	// Currently, the INF specific routing table is not used. by David.
	//$hasroute=0;
	//if ($hasroute>0) echo "ip -6 rule add table ".$_GLOBALS["INF"]." prio 30000\n";
	if ($hasevent>0)
	{
		cmd("event ".$_GLOBALS["INF"].".UP");
		cmd("echo 1 > /var/run/".$_GLOBALS["INF"].".UP");
	}
}

function main_entry()
{
	if ($_GLOBALS["INF"]=="") return "No INF !!";
	if		($_GLOBALS["ACTION"]=="ATTACH") return dev_attach(1);
	else if	($_GLOBALS["ACTION"]=="DETACH") return dev_detach(1);
	return "Unknown action - ".$_GLOBALS["ACTION"];
}

/*****************************************/
/* Required variables:
 *
 *	ACTION:		ATTACH/DETACH
 *	MODE:		IPv6 mode
 *	INF:		Interface UID
 *	DEVNAM:		device name
 *	IPADDR:		IP address
 *	PREFIX:		Prefix length
 *	GATEWAY:	Gateway
 *	ROUTERLFT:	Router lift time
 *	PREFERLFT:	Prefer lift time
 *	VALIDLFT:	Valid lift time
 *	DNS:		DNS servers
 */
$ret = main_entry();
if ($ret!="")	cmd("# ".$ret."\nexit 9\n");
else			cmd("exit 0\n");
?>
