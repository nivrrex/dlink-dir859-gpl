<?
/* We use VID 2 for WAN port, VID 1 for LAN ports.
 * by David Hsieh <david_hsieh@alphanetworks.com> */
include "/htdocs/phplib/xnode.php";
include "/htdocs/phplib/trace.php";
include "/htdocs/phplib/phyinf.php";

function startcmd($cmd)	{fwrite(a,$_GLOBALS["START"], $cmd."\n");}
function stopcmd($cmd)	{fwrite(a,$_GLOBALS["STOP"], $cmd."\n");}
function error($errno)	{startcmd("exit ".$errno); stopcmd("exit ".$errno);}
function layout_bridge()
{
	SHELL_info($START, "LAYOUT: Start bridge layout ...");

	/* Start .......................................................................... */
	/* Config RTL8367 as bridge mode layout. */
	//setup_switch("bridge");

	/* Using WAN MAC address during bridge mode. */
	$mac = PHYINF_getdevdatamac("lanmac");    //PHYINF_getmacsetting("BRIDGE-1");
	if($mac == "")
	{
		$mac = "00:11:22:33:44:55";	
	} 
    	if ($mac != "")	
    	{
	        //work around +  for apcli mac
	        $mactmp=cut($mac, 5, ":");  
	        $mactmp=strtoul($mactmp, 16);
	        $validmac = $mactmp%4;
		if($validmac != 1)
		{
			TRACE_error("====================ERROR!!=====================");
			TRACE_error("The mac setting is wrong.\nThis will casue wifi work abnormal.\nAdjust the mac for WIFI.");
			TRACE_error("================================================");
		}			
	        $mactmp = $mactmp - $validmac;
		//re-cal all macs.
		$mac5=dec2strf("%x", $mactmp);
	        $wlan_mac=cut($mac, 0, ":").":".cut($mac, 1, ":").":".cut($mac, 2, ":").":".cut($mac, 3, ":").":".cut($mac, 4, ":").":".$mac5;
	        $mac5=dec2strf("%x", $mactmp+1);
	        $newlanmac=cut($mac, 0, ":").":".cut($mac, 1, ":").":".cut($mac, 2, ":").":".cut($mac, 3, ":").":".cut($mac, 4, ":").":".$mac5;
	        $mac=$newlanmac;
	        
	        //work around - 
	        del("/runtime/devdata/wlanmac");
	        set("/runtime/devdata/wlanmac",$wlan_mac);
    	}

	/* Create bridge interface. */
	startcmd("brctl addbr br0; brctl stp br0 off; brctl setfd br0 0");
	//startcmd("brctl addif br0 eth2");
	startcmd("ip link set br0 up");

	/* Setup the runtime nodes. */
	PHYINF_setup("ETH-1", "eth", "br0");

	//+++ hendry, for wifi topology
	$p = XNODE_getpathbytarget("", "phyinf", "uid", "ETH-1", 0);
	set($p."/bridge/ports/entry:1/uid",		"MBR-1");
	set($p."/bridge/ports/entry:1/phyinf",	"BAND24G-1.1");
	$p = XNODE_getpathbytarget("", "phyinf", "uid", "ETH-1", 0);
	set($p."/bridge/ports/entry:2/uid",		"MBR-2");
	set($p."/bridge/ports/entry:2/phyinf",	"BAND24G-1.2");
	$p = XNODE_getpathbytarget("", "phyinf", "uid", "ETH-1", 0);
	set($p."/bridge/ports/entry:3/uid",		"MBR-3");
	set($p."/bridge/ports/entry:3/phyinf",	"BAND24G-1.3");
	//--- hendry

	/* Done */
	startcmd("xmldbc -s /runtime/device/layout bridge");
	startcmd("usockc /var/gpio_ctrl BRIDGE");
//	startcmd("service ENLAN start");
	startcmd("service PHYINF.ETH-1 alias PHYINF.BRIDGE-1");
	startcmd("service PHYINF.ETH-1 start");

	/* ip alias */

//	$mactmp = cut($mac, 4, ":");  $mac4 = strtoul($mactmp, 16);
//	$mactmp = cut($mac, 5, ":");  $mac5 = strtoul($mactmp, 16);

	/* skip 169.254.0.0 & 169.254.255.255 */
//	if($mac4 == "0" && $mac5 == "0") $aip = "169.254.0.1";
//	else if($mac4 == "255" && $mac5 == "255") $aip = "169.254.0.1";
//	else $aip = "169.254.".$mac4.".".$mac5;

	startcmd("ip addr add 192.168.0.50/24 broadcast 192.168.0.255 dev br0");

	//startcmd("ifconfig br0 192.168.0.50 up");
	//startcmd("ifconfig br0:1 ".$aip." up");
	$p = XNODE_getpathbytarget("/runtime", "inf", "uid", "BRIDGE-1", 1);
	set($p."/ipalias/cnt",			1);
	set($p."/ipalias/ipv4/ipaddr:1",		"192.168.0.50");
	set($p."/devnam","br0");

	set($p."/inet/addrtype","ipv4"); 
	set($p."/inet/ipv4/valid","1"); 
	set($p."/inet/ipv4/ipaddr","192.168.0.50"); 
	set($p."/inet/ipv4/mask","24"); 
	
	startcmd("ip link set eth2 addr ".$mac);
	
	/* enable all embeded switch rt3052 phys ports */
	if(query("/device/enlan")==1 || query("/runtime/devdata/mfcmode")!="")
	{	
		startcmd("echo \"write 0 0 0x3100\" > /proc/rt3052/mii/ctrl");
		startcmd("echo \"write 1 0 0x3100\" > /proc/rt3052/mii/ctrl");
		startcmd("echo \"write 2 0 0x3100\" > /proc/rt3052/mii/ctrl");
		startcmd("echo \"write 3 0 0x3100\" > /proc/rt3052/mii/ctrl");
		startcmd("echo \"write 4 0 0x3100\" > /proc/rt3052/mii/ctrl");
		startcmd("brctl addif br0 eth2");
		startcmd("ip link set eth2 up");
	}
	else
	{
		startcmd("echo \"write 0 0 0x800\" > /proc/rt3052/mii/ctrl");
		startcmd("echo \"write 1 0 0x800\" > /proc/rt3052/mii/ctrl");
		startcmd("echo \"write 2 0 0x800\" > /proc/rt3052/mii/ctrl");
		startcmd("echo \"write 3 0 0x800\" > /proc/rt3052/mii/ctrl");
		startcmd("echo \"write 4 0 0x800\" > /proc/rt3052/mii/ctrl");
		startcmd("brctl addif br0 eth2");
		
	}
	/* Stop ........................................................................... */
	SHELL_info($STOP, "LAYOUT: Stop bridge layout ...");
	stopcmd("service PHYINF.ETH-1 stop");
	stopcmd("service PHYINF.BRIDGE-1 delete");
	stopcmd('xmldbc -s /runtime/device/layout ""');
	stopcmd("/etc/scripts/delpathbytarget.sh /runtime phyinf uid ETH-1");
	//stopcmd("brctl delif br0 rai0");
	//stopcmd("brctl delif br0 ra0");
	//stopcmd("brctl delif br0 eth2");
	//stopcmd("ip link set eth2 down");
	stopcmd("brctl delbr br0");
	//stopcmd("rtlioc initvlan");
	return 0;
}

/* everything starts from here !! */
fwrite("w",$START, "#!/bin/sh\n");
fwrite("w", $STOP, "#!/bin/sh\n");

$ret = 9;
//
$layout	= query("/device/layout");
$layout	= "bridge";
startcmd("ifconfig lo up");
stopcmd("ifconfig lo down");

if ($layout=="apclient")
{
	SHELL_info($STOP, "LAYOUT: to do ap client ...");
}
else if ($layout=="bridge")
{
	$ret = layout_bridge();
}

$p = XNODE_getpathbytarget("/runtime", "phyinf", "uid", "ETH-1", 0);
add($p."/bridge/port",  "BAND24G-1.1");
add($p."/bridge/port",  "BAND24G-1.3");
$p = XNODE_getpathbytarget("/runtime", "phyinf", "uid", "ETH-2", 0);
add($p."/bridge/port",  "BAND24G-1.2");

//startcmd("usockc /var/gpio_ctrl GPIO_SWITCH");
//startcmd("/etc/scripts/setswitch.sh `cat /var/gpio_ctrl_result`");

startcmd("sleep 1");
startcmd("service INFSVCS.BRIDGE-1 start");
stopcmd("service INFSVCS.BRIDGE-1 stop");

startcmd("service PHYINF.WIFI start");
stopcmd("service PHYINF.WIFI stop");

error($ret);
?>
