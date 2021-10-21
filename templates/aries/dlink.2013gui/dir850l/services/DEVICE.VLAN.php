<?
include "/htdocs/phplib/trace.php";

fwrite("w",$START, "#!/bin/sh\n");
fwrite("w", $STOP, "#!/bin/sh\n");


function startcmd($cmd)	{fwrite(a,$_GLOBALS["START"], $cmd."\n");}
function stopcmd($cmd)	{fwrite(a,$_GLOBALS["STOP"], $cmd."\n");}


//set vlan id, when id=0 then turn off tag
function setvlan($dev,$vlan_id,$internetid)
{
/*
    global_vlan: 0  (1 is active)
    is_lan: 1		(1 is lan)
    vlan_enable: 0  (1 is active)
    vlan_tag: 0     (1 is active)
    vlan_id: 0		(1~4096)
    vlan_pri: 0
    vlan_cfi: 0
    vlan_forwarding_rule: 0
*/
	
	TRACE_debug("vlan_id = ".$vlan_id);
	TRACE_debug("interid = ".$internetid);
	
	if($dev == "eth1")//set wan tag
	{
		startcmd('echo 1 0 1 1 '.$internetid.' 0 0 2 > /proc/'.$dev.'/mib_vlan');
		stopcmd('echo 0 0 0 0 0 0 0 0 > /proc/'.$dev.'/mib_vlan');
	}
	else if($dev == "eth7")//set bridge tag
	{
		startcmd('echo 1 0 1 0 '.$internetid.' 0 0 1 > /proc/'.$dev.'/mib_vlan');
		stopcmd('echo 0 1 0 0 0 0 0 0 > /proc/'.$dev.'/mib_vlan');
	}
	else
	{
		if($vlan_id != $internetid)//if tag is not ethernet, then set bridge mode
		{
			startcmd('echo 1 1 1 1 '.$vlan_id.' 0 0 1 > /proc/'.$dev.'/mib_vlan');
		}
		else
		{
			startcmd('echo 1 1 1 1 '.$vlan_id.' 0 0 2 > /proc/'.$dev.'/mib_vlan');
		}
		//turn off vlan tag
		stopcmd('echo 0 1 0 0 0 0 0 0 > /proc/'.$dev.'/mib_vlan');
	}
	
}

/* turn on/off guest access EX:ping or other access for wlan0-va0~va3*/
/* 0 is off, 1 is on*/
function guest_access($dev,$sw)
{
	startcmd('iwpriv '.$dev.' set_mib guest_access='.$sw);
}



/*set lan mac and set each lan up*/
function set_lan($dev,$mac)
{
	startcmd('ifconfig '.$dev.' hw ether '.$mac);
	startcmd('ifconfig '.$dev.' up');
	startcmd('brctl addif br0 '.$dev);
}


$vlan_path	= "/device/vlan/lanport/";
$vwlan_path = "/device/vlan/wlanport/";
$vlanenable = query("/device/vlan/active");
$mac_addr   = query("/runtime/devdata/lanmac");
$layout		= query("/device/layout");

$interid	= query("/device/vlan/interid");
$voipid		= query("/device/vlan/voipid");
$iptvid		= query("/device/vlan/iptvid");

$lan1id = query($vlan_path."lan1");
$lan2id = query($vlan_path."lan2");
$lan3id = query($vlan_path."lan3");
$lan4id = query($vlan_path."lan4");

$wlan01id = query($vwlan_path."wlan01");
$wlan02id = query($vwlan_path."wlan02");
$wlan03id = query($vwlan_path."wlan03");
$wlan04id = query($vwlan_path."wlan04");
$wlan11id = query($vwlan_path."wlan11");
$wlan12id = query($vwlan_path."wlan12");
$wlan13id = query($vwlan_path."wlan13");
$wlan14id = query($vwlan_path."wlan14");

//==del all bridge device==//
//startcmd('brctl delif br0 eth0');
stopcmd('ifconfig eth0 down');
stopcmd('ifconfig eth2 down');
stopcmd('ifconfig eth3 down');
stopcmd('ifconfig eth4 down');
stopcmd('ifconfig eth7 down');
stopcmd('brctl delif br0 eth2');
stopcmd('brctl delif br0 eth3');
stopcmd('brctl delif br0 eth4');
stopcmd('brctl delif br0 eth7');

if($vlanenable == "1" && $layout == "router")
{
	startcmd('echo 1 > /proc/rtk_vlan_support');
	
	setvlan("eth1","",$interid);//set wan tag
	setvlan("eth7","",$interid);//set bridge tag
	setvlan("eth0",$lan1id,$interid);
	setvlan("eth2",$lan2id,$interid);
	setvlan("eth3",$lan3id,$interid);
	setvlan("eth4",$lan4id,$interid);
	setvlan("wlan0",    $wlan01id,$interid);
	setvlan("wlan0-va0",$wlan02id,$interid);
	setvlan("wlan0-va1",$wlan03id,$interid);
	setvlan("wlan0-va2",$wlan04id,$interid);
	setvlan("wlan1",    $wlan11id,$interid);
	setvlan("wlan1-va0",$wlan12id,$interid);
	setvlan("wlan1-va1",$wlan13id,$interid);
	setvlan("wlan1-va2",$wlan14id,$interid);
	
	set_lan("eth0",$mac_addr);
	set_lan("eth2",$mac_addr);
	set_lan("eth3",$mac_addr);
	set_lan("eth4",$mac_addr);
	set_lan("eth7",$mac_addr);
	
	
}
else
{
	startcmd('ifconfig eth0 up');
	/*
	startcmd('ifconfig eth2 down');
	startcmd('ifconfig eth3 down');
	startcmd('ifconfig eth4 down');
	startcmd('ifconfig eth7 down');
	startcmd('brctl delif br0 eth2');
	startcmd('brctl delif br0 eth3');
	startcmd('brctl delif br0 eth4');
	startcmd('brctl delif br0 eth7');
	//==clear vlan data==//
	setvlan("eth1","0","");//set wan tag
	setvlan("eth7","0","");//set bridge tag
	setvlan("eth0","0","");
	setvlan("eth2","0","");
	setvlan("eth3","0","");
	setvlan("eth4","0","");
	setvlan("wlan0",    "0","");
	setvlan("wlan0-va0","0","");
	setvlan("wlan0-va1","0","");
	setvlan("wlan0-va2","0","");
	setvlan("wlan1",    "0","");
	setvlan("wlan1-va0","0","");
	setvlan("wlan1-va1","0","");
	setvlan("wlan1-va2","0","");
*/

	/*
	$vlan_proc = fread("", "/proc/rtk_vlan_support");
	if($vlan_proc == "rtk_vlan_support_enable: 1")
	{
		TRACE_debug("test".$vlan);
		startcmd('echo 0 > /proc/rtk_vlan_support');
	}
	*/
}

/* Done */
fwrite("a",$START, "exit 0\n");
fwrite("a", $STOP, "exit 0\n");
?>
