<?
/* vi: set sw=4 ts=4:

	PORT	Switch Port	VID
	====	===========	===
	CPU_W	PORT0		2
	WAN		PORT5		2
	CPU_L	PORT6		1
	LAN1	PORT1		1
	LAN2	PORT2		1
	LAN3	PORT3		1
	LAN4	PORT4		1

*/
include "/htdocs/phplib/xnode.php";
include "/htdocs/phplib/trace.php";
include "/htdocs/phplib/phyinf.php";

function startcmd($cmd)	{fwrite(a,$_GLOBALS["START"], $cmd."\n");}
function stopcmd($cmd)	{fwrite(a,$_GLOBALS["STOP"], $cmd."\n");}
function error($errno)	{startcmd("exit ".$errno); stopcmd("exit ".$errno);}

function acl_bwc_check()
{
	$acl_bwc_enable = 0;

	foreach ("/bwc/entry")
	{
		if (query("enable") == 1 && query("uid") != "" )
		{
			$acl_bwc_enable = 1;
		}
	}

	if(query("/acl/accessctrl/enable")=="1")
	{
		foreach ("/acl/accessctrl/entry")
		{
			if(query("webfilter/enable") == "1" || query("webfilter/logging") == "1")
			{
				$acl_bwc_enable = 1;
			}
		}
	}

	return $acl_bwc_enable;
}

function setup_router_switch()
{
	startcmd("swconfig dev switch0 set reset 1");
	startcmd("swconfig dev switch0 vlan 2 set ports '0 5'");
	startcmd("swconfig dev switch0 vlan 1 set ports '1 2 3 4 6'");
	startcmd("swconfig dev switch0 set enable_vlan 1");
	startcmd("swconfig dev switch0 set apply 1");
}

function setup_bridge_switch()
{
	startcmd("swconfig dev switch0 set reset 1");
	startcmd("swconfig dev switch0 vlan 1 set ports '1 2 3 4 5 6'");
	startcmd("swconfig dev switch0 set enable_vlan 1");
	startcmd("swconfig dev switch0 set apply 1");
}

function layout_bridge()
{
	SHELL_info($START, "LAYOUT: Start bridge layout ...");

	/* Start .......................................................................... */
	/* Config VLAN as bridge layout. */
	//disable switch VLAN configuration
	$mac = PHYINF_gettargetmacaddr("1BRIDGE", "ETH-1");
	if ($mac=="") $mac="00:de:fa:30:50:10";

	startcmd("ip link set eth1 up");
	setup_bridge_switch();

	startcmd("ifconfig eth1 allmulti");
	startcmd("ip link set eth1 addr ".$mac);

	/* Create bridge interface. */
	startcmd("brctl addbr br0; brctl stp br0 off; brctl setfd br0 0");
	startcmd('brctl addif br0 eth1');
	startcmd('ip link set br0 up');
	/*for https need lo interface*/
	startcmd('ip link set lo up');
	/*for bridge 192.168.0.50 alias ip access*/
	startcmd('ifconfig br0:1 192.168.0.50 up');
	startcmd('service HTTP restart');
	/* Setup the runtime nodes. */
	PHYINF_setup("ETH-1", "eth", "br0");

	/* Done */
	startcmd('xmldbc -s /runtime/device/layout bridge');
	startcmd('usockc /var/gpio_ctrl BRIDGE');
	startcmd('service ENLAN start');
	startcmd('service PHYINF.ETH-1 alias PHYINF.BRIDGE-1');
	startcmd('service PHYINF.ETH-1 start');

	/*$p = XNODE_getpathbytarget("/runtime", "phyinf", "uid", "ETH-1", 0);
	add($p."/bridge/port",	"WIFI-STA");*/

	/* Stop ........................................................................... */
	SHELL_info($STOP, "LAYOUT: Stop bridge layout ...");
	stopcmd("service PHYINF.ETH-1 stop");
	stopcmd('service PHYINF.BRIDGE-1 delete');
	stopcmd('xmldbc -s /runtime/device/layout ""');
	stopcmd('/etc/scripts/delpathbytarget.sh /runtime phyinf uid ETH-1');
	stopcmd('brctl delif br0 eth1');
	stopcmd('ip link set eth1 down');
	/*for bridge 192.168.0.50 alias ip access*/
	stopcmd('ifconfig br0:1 down');
	stopcmd('ip link set br0 down');
	stopcmd('brctl delbr br0');
	return 0;
}

function layout_router($mode)
{
	SHELL_info($START, "LAYOUT: Start router layout ...");

	$qos_en = get("","/bwc/entry:1/enable");

	/* Start .......................................................................... */
	/* Config VLAN as router mode layout. (1 WAN + 4 LAN) */
	$lanmac = PHYINF_gettargetmacaddr($mode, "ETH-1");
	if		($mode=="1W1L") $wanmac = PHYINF_gettargetmacaddr("1W1L", "ETH-2");
	else if	($mode=="1W2L") $wanmac = PHYINF_gettargetmacaddr("1W2L", "ETH-3");
	if ($wanmac=="") $wanmac = "00:de:fa:30:50:10";
	if ($lanmac=="") $lanmac = "00:de:fa:30:50:00";

	setup_router_switch();

	startcmd("ifconfig eth1 allmulti");

	/* Setup MAC address */
	/* Check User configuration for WAN port. */
	startcmd("ip link set eth1 addr ".$lanmac);
	startcmd("ip link set eth1 up");
	startcmd("ip link set eth0 addr ".$wanmac);
	startcmd("ip link set eth0 up");

	/*for https need lo interface*/
	startcmd('ip link set lo up');
	
	/* Create bridge interface. */
	startcmd("brctl addbr br0; brctl stp br0 off; brctl setfd br0 0;");
	startcmd("brctl addif br0 eth1");
	startcmd("ip link set br0 up");

	if ($mode=="1W2L")
	{
		startcmd("brctl addbr br1; brctl stp br1 off; brctl setfd br1 0;");
		startcmd("ip link set br1 up");
	}

	/* Setup the runtime nodes. */
	$Wan_index_number = query("/device/router/wanindex");
	if ($mode=="1W1L")
	{
		PHYINF_setup("ETH-1", "eth", "br0");
		PHYINF_setup("ETH-2", "eth", "eth0");

		/* set Service Alias */
		startcmd('service PHYINF.ETH-1 alias PHYINF.LAN-1');
		startcmd('service PHYINF.ETH-2 alias PHYINF.WAN-1');
		/* WAN: set extension nodes for linkstatus */
		$path = XNODE_getpathbytarget("/runtime", "phyinf", "uid", "ETH-2", 0);
		startcmd('xmldbc -x '.$path.'/linkstatus "get:psts -i '.$Wan_index_number.'"');
	}
	else if ($mode=="1W2L")
	{
		PHYINF_setup("ETH-1", "eth", "br0");
		PHYINF_setup("ETH-2", "eth", "br1");
		PHYINF_setup("ETH-3", "eth", "eth0");

		/* set Service Alias */
		startcmd('service PHYINF.ETH-1 alias PHYINF.LAN-1');
		startcmd('service PHYINF.ETH-2 alias PHYINF.LAN-2');
		startcmd('service PHYINF.ETH-3 alias PHYINF.WAN-1');
		/* WAN: set extension nodes for linkstatus */
		$path = XNODE_getpathbytarget("/runtime", "phyinf", "uid", "ETH-3", 0);
		startcmd('xmldbc -x '.$path.'/linkstatus "get:psts -i '.$Wan_index_number.'"');
	}

	/* LAN: set extension nodes for linkstatus */
	$path = XNODE_getpathbytarget("/runtime", "phyinf", "uid", "ETH-1", 0);

	startcmd('xmldbc -x '.$path.'/linkstatus:1 "get:psts -i 1"');
	startcmd('xmldbc -x '.$path.'/linkstatus:2 "get:psts -i 2"');
	startcmd('xmldbc -x '.$path.'/linkstatus:3 "get:psts -i 3"');
	startcmd('xmldbc -x '.$path.'/linkstatus:4 "get:psts -i 4"');

	/* Done */
	startcmd("xmldbc -s /runtime/device/layout router");
	startcmd("xmldbc -s /runtime/device/router/mode ".$mode);
	startcmd("usockc /var/gpio_ctrl ROUTER");
	startcmd("service PHYINF.ETH-1 start");
	startcmd("service PHYINF.ETH-2 start");
	if ($mode=="1W2L") startcmd("service PHYINF.ETH-3 start");

	/* Stop ........................................................................... */
	SHELL_info($STOP, "LAYOUT: Stop router layout ...");
	if ($mode=="1W2L")
	{
		stopcmd("service PHYINF.ETH-3 stop");
		stopcmd("service PHYINF.LAN-2 delete");
	}
	stopcmd('service PHYINF.ETH-2 stop');
	stopcmd('service PHYINF.ETH-1 stop');
	stopcmd('service PHYINF.WAN-1 delete');
	stopcmd('service PHYINF.LAN-1 delete');
	stopcmd('xmldbc -s /runtime/device/layout ""');
	stopcmd('/etc/scripts/delpathbytarget.sh /runtime phyinf uid ETH-1');
	stopcmd('/etc/scripts/delpathbytarget.sh /runtime phyinf uid ETH-2');
	stopcmd('/etc/scripts/delpathbytarget.sh /runtime phyinf uid ETH-3');
	stopcmd('brctl delif br0 eth1');
	stopcmd('ip link set eth1 down');
	stopcmd('ip link set eth0 down');
	stopcmd('ip link set br0 down');
	stopcmd('brctl delbr br0; brctl delbr br1');
	return 0;
}

/* everything starts from here !! */
fwrite("w",$START, "#!/bin/sh\n");
fwrite("w", $STOP, "#!/bin/sh\n");

$ret = 9;
$layout = query("/device/layout");
if ($layout=="router")
{
	/* only 1W1L & 1W2L supported for router mode. */
	$mode = query("/device/router/mode"); if ($mode!="1W1L") $mode = "1W2L";
	$ret = layout_router($mode);
}
else if ($layout=="bridge")
{
	$ret = layout_bridge();
}

	$p = XNODE_getpathbytarget("/runtime", "phyinf", "uid", "ETH-1", 0);
	add($p."/bridge/port",	"BAND24G-1.1");	
	add($p."/bridge/port",	"BAND5G-1.1");	
	$p = XNODE_getpathbytarget("/runtime", "phyinf", "uid", "ETH-2", 0);
	add($p."/bridge/port",	"BAND24G-1.2");	
	add($p."/bridge/port",	"BAND5G-1.2");	

/* driver is not installed yet, we move this to s52wlan (tom, 20120405) */
/* startcmd("service PHYINF.WIFI start");*/ 
stopcmd("service PHYINF.WIFI stop");

error($ret);

?>
