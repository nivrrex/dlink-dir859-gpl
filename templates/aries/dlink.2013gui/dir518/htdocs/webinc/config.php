<?	
	$REBOOTTIME = 65;

	$WAN1  = "WAN-1";
	$WAN2  = "WAN-2";
	$WAN3  = "WAN-3";	
	$WAN4  = "WAN-4";
	$WAN7  = "WAN-7";
	$WAN6  = "WAN-6"; /* IPv6 link local */
	$WLAN1 = "BAND24G-1.1";
	$WLAN1_GZ = "BAND24G-1.2";
	$WLAN2 = "BAND5G-1.1";
	$WLAN2_GZ = "BAND5G-1.2";
	$WLAN1_REP	 = "BAND24G-REPEATER";
	$WLAN2_REP	 = "BAND5G-REPEATER";
	$LAN1  = "LAN-1";
	$LAN2  = "LAN-2";
	$LAN3  = "LAN-3"; /* IPv6 link local interface */
	$LAN4  = "LAN-4"; /* IPv6 Global interface */
	$LAN5  = "LAN-5"; /* IPv6 Guest zone interface */
	
	$SRVC_WLAN = "PHYINF.WIFI";
	$BAND24G_DEVNAME = "wlan1";
	$BAND24G_GUEST_DEVNAME = "wlan1-va0";
	$BAND24G_REPEATER_DEVNAME = "wlan1-vxd";
	$BAND5G_DEVNAME = "wlan0";
	$BAND5G_GUEST_DEVNAME = "wlan0-va0";
	$BAND5G_REPEATER_DEVNAME = "wlan0-vxd";

	$FEATURE_NOSCH = 0;			/* if this model doesn't support scheudle, set it as 1. */
	$FEATURE_NOPPTP = 0;		/* if this model doesn't support PPTP, set it as 1. */
	$FEATURE_NOL2TP = 0;		/* if this model doesn't support L2TP, set it as 1.*/
	$FEATURE_NOLAN = 1;

	if(query("/runtime/devdata/countrycode")=="RU")
	{
		$FEATURE_NORUSSIAPPTP = 0;	/* if this model doesn't support Russia PPTP, set it as 1.*/
		$FEATURE_NORUSSIAPPPOE = 0;	/* if this model doesn't support Russia PPPoE, set it as 1. */
		$FEATURE_NORUSSIAL2TP = 0; 	/* if this model doesn't support Russia L2TP, set it as 1. */
	}
	else
	{
		$FEATURE_NORUSSIAPPTP = 1;	/* if this model doesn't support Russia PPTP, set it as 1.*/
		$FEATURE_NORUSSIAPPPOE = 1;	/* if this model doesn't support Russia PPPoE, set it as 1. */
		$FEATURE_NORUSSIAL2TP = 1; 	/* if this model doesn't support Russia L2TP, set it as 1. */
	}

	if(query("/runtime/device/langcode") == "zhcn")
	{
		 $FEATURE_DLINK_COM_CN = 1; /* if this model supports dlink.com.cn, set it as 1. */
		 $FEATURE_ORAY = 1; /* if this model supports ORAY, set it as 1. */
		 $FEATURE_CHINA_SPECIAL_WAN = 1; /* if this model supports China special wan, set it as 1. */
		 $FEATURE_DHCPPLUS = 1; /* if this model supports DHCP+, set it as 1. */
		 $FEATURE_CHINA = 1;
	}
	else
	{
		 $FEATURE_DLINK_COM_CN = 0;
		 $FEATURE_ORAY= 0;
		 $FEATURE_CHINA_SPECIAL_WAN = 0;
		 $FEATURE_DHCPPLUS = 0;
		 $FEATURE_CHINA = 0;
	}

	$FEATURE_NOEASYSETUP = 0;	/* if this model has no easy setup page, set it as 1. */
	$FEATURE_NOIPV6 = 0;	/* if this model has no IPv6, set it as 1. */
	$FEATURE_NOAPMODE = 1; /* if this model has no access point mode, set it as 1. */
	$FEATURE_HAVEBGMODE = 0; /* if this model has bridge mode, set it as 1.*/
	$FEATURE_INBOUNDFILTER = 1;	/* if this model has inbound filter, set it as 1.*/
	$FEATURE_DUAL_BAND = 1;		/* if this model has 5 Ghz, set it as 1.*/
	$FEATURE_NOACMODE = 0; /* if this model has no wireless ac mode, set it as 1.*/
	$FEATURE_NOGUESTZONE = 0;
	$FEATURE_NODSLITE = 0; /* if this model has no DS-Lite, set it as 1.*/
	$FEATURE_NATENDPOINT = 0; /* if this model has NAT Endpoint, set it as 1.*/
	$FEATURE_ANTENNA = 1T1R; /* Set it as 1T1R / 2T2R / 3T3R. */
	$FEATURE_NOSAMBA = 0; /* if this model has no SAMBA, set it as 1.*/
?>
