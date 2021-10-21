<?
//this script needs argument EVENT, we need this to control WAN LED

include "/htdocs/phplib/xnode.php";

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

if($EVENT == "WAN_CONNECTED")
{
	echo "wan_port_status=`psts wan`\n";
	echo "if [ \"$wan_port_status\" != \"\" ]; then\n";
	echo "usockc /var/gpio_ctrl INET_GREEN\n";
	echo "fi\n";
}

if($EVENT == "WAN_DISCONNECTED")
{
	$inet_led_blinking = 0;
	$infp = XNODE_getpathbytarget("", "inf", "uid", "WAN-1", 0);
	$inet   = query($infp."/inet");
	$inetp = XNODE_getpathbytarget("/inet", "entry", "uid", $inet, 0);
	$addrtype = query($inetp."/addrtype");

	$schedule = query($infp."/schedule");
	if($schedule != "")
	{
		$inet_led_blinking = 1;
	}

	if($addrtype != "ipv4" && $addrtype != "ipv6")
	{
		$dialmode = query($inetp."/".$addrtype."/dialup/mode");
		if($dialmode == "manual" || $dialmode == "ondemand")
        	{
			$inet_led_blinking = 1;
		}
	}

	echo "wan_port_status=`psts wan`\n";
	echo "if [ \"$wan_port_status\" != \"\" ]; then\n";
	if($inet_led_blinking == 1)
	{
		echo "usockc /var/gpio_ctrl INET_AMBER_BLINK\n";
	}
	else
	{
		echo "usockc /var/gpio_ctrl INET_AMBER\n";
	}
	echo "else usockc /var/gpio_ctrl INET_NONE\n";
    echo "fi\n";
}

if($EVENT == "WAN_PPP_ONDEMAND")
{
	echo "usockc /var/gpio_ctrl INET_AMBER_BLINK\n";
}

if($EVENT == "WAN_PPP_MANUAL")
{
        echo "usockc /var/gpio_ctrl INET_AMBER_BLINK\n";
}

if($EVENT == "WAN_PPP_SCHEDULE")
{
        echo "usockc /var/gpio_ctrl INET_AMBER_BLINK\n";
}

if($EVENT == "WAN_LINKUP")
{
	if(wan_has_ip() != 0)
	{
		echo "usockc /var/gpio_ctrl INET_GREEN\n";
	}
}

if($EVENT == "WAN_LINKDOWN")
{
	echo "usockc /var/gpio_ctrl INET_NONE\n";
}

?>
