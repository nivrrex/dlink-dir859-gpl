<?
//this script needs argument EVENT, we need this to control WAN LED

include "/htdocs/phplib/xnode.php";
include "/htdocs/webinc/config.php";
include "/htdocs/phplib/trace.php";

echo "#!/bin/sh\n";

function wan_has_ip()
{
	$wan_inf = XNODE_getpathbytarget("/runtime", "inf", "uid", "WAN-1", 0);
	if($wan_inf == "")
		return 0;

	$addrtype = get("x", $wan_inf."/inet/addrtype");
	$ipaddr = "";

	if($addrtype == "ppp4")
	{
		$ipaddr = get("x", $wan_inf."/inet/ppp4/local");
	}

	if($addrtype == "ipv4")
	{
		$ipaddr = get("x", $wan_inf."/inet/ipv4/ipaddr");
	}

	if($ipaddr == "")
	{
		return 0;
	}
	else
	{
		return 1;
	}
}

function get_wan_type()
{
	$path_inf_wan1 = XNODE_getpathbytarget("", "inf", "uid", "WAN-1", 0);
	$path_inf_wan2 = XNODE_getpathbytarget("", "inf", "uid", "WAN-2", 0);
	$wan1_inet = query($path_inf_wan1."/inet"); 
	$wan2_inet = query($path_inf_wan2."/inet");
	$path_wan1_inet = XNODE_getpathbytarget("/inet", "entry", "uid", $wan1_inet, 0);
	$path_wan2_inet = XNODE_getpathbytarget("/inet", "entry", "uid", $wan2_inet, 0);	
	$Type="";
	
	$mode=query($path_wan1_inet."/addrtype");
	if($mode == "ipv4")
	{
		anchor($path_wan1_inet."/ipv4");
		if(query("ipv4in6/mode") == "dslite")	//-----DS-Lite
			{	$Type="DsLite";	}	
		else if(query("static") == 1) //-----Static     
			{	$Type="Static";	}
		else if(query("static") == 0) //-----DHCP
			{	$Type="DHCP"; }
	}
	else if($mode == "ppp10" && query($path_wan1_inet."/ppp4/over") == "eth") //-----PPPoE
	{
		anchor($path_wan1_inet."/ppp4/dialup");
		if(query("mode") == "auto")
			{	$Type="DHCPPPPoE";}
		else
			{	$Type="OthercPPPoE";}
	}
	else if($mode == "ppp4" && query($path_wan1_inet."/ppp4/over") == "eth") //-----PPPoE
	{
		anchor($path_wan1_inet."/ppp4/dialup");
		if(query("mode") == "auto")
			{	$Type="DHCPPPPoE";}
		else
			{	$Type="OthercPPPoE";}
	}
	else if($mode == "ppp4" && query($path_wan1_inet."/ppp4/over") == "pptp")	//-----PPTP
	{
		anchor($path_wan2_inet."/ipv4");
		if(query("static") == 1)
			{	$Type="StaticPPTP";	}
		else
			{	$Type="DynamicPPTP";}
	}
	else if($mode == "ppp4" && query($path_wan1_inet."/ppp4/over") == "l2tp")	//-----L2TP
	{
		anchor($path_wan2_inet."/ipv4");
		if(query("static") == 1)
			{	$Type="StaticL2TP"; }
		else
			{	$Type="DynamicL2TP";}
	}
	
	return $Type;
}


if($EVENT == "WAN_CONNECTED")
{
	echo "usockc /var/gpio_ctrl INET_ON\n";
}

if($EVENT == "WAN_DISCONNECTED")
{
	if(get_wan_type() == "DHCP" || get_wan_type() == "DHCPPPPoE")		
		{ echo "usockc /var/gpio_ctrl INET_OFF\n"; }
	else														
		{ echo "usockc /var/gpio_ctrl INET_BLINK_SLOW\n"; }
}

if($EVENT == "WAN_PPP_ONDEMAND")
{
	echo "usockc /var/gpio_ctrl INET_BLINK_SLOW\n";
}

if($EVENT == "WAN_LINKUP")
{
	if(wan_has_ip() != 0)		
	{
		echo "usockc /var/gpio_ctrl INET_ON\n";	
	}else{
		if(get_wan_type() == "DHCP" || get_wan_type() == "DHCPPPPoE")
			{ echo "usockc /var/gpio_ctrl INET_OFF\n"; }
		else
			{ echo "usockc /var/gpio_ctrl INET_BLINK_SLOW\n"; }
	}
}

if($EVENT == "WAN_LINKDOWN")
{
	echo "usockc /var/gpio_ctrl INET_OFF\n";
}

if($EVENT == "WAN_RENEW")
{
	echo "usockc /var/gpio_ctrl INET_OFF\n";
}
?>
