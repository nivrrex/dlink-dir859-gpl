HTTP/1.1 200 OK
Content-Type: text/xml; charset=utf-8

<?
echo "\<\?xml version='1.0' encoding='utf-8'\?\>";
include "/htdocs/phplib/xnode.php";
include "/htdocs/webinc/config.php";
include "/htdocs/phplib/trace.php";

foreach("/inf")
{
	$uid = query("uid");
	if(strstr($uid, "WAN")!="")
	{
		$active = query("active");
		$name = query("name");
		/* this app is for IPv4 only, it should avoid to get the data of IPv6. */
		if($active==1 && $name=="")
		{
			$active_wan = $uid;
			break;
		}
	}
}

$mac = query("/runtime/devdata/wanmac");

if($active_wan==$WAN1) /* for ethernet */
{
	$path_inf_wan1 = XNODE_getpathbytarget("", "inf", "uid", $WAN1, 0);
	$path_inf_wan2 = XNODE_getpathbytarget("", "inf", "uid", $WAN2, 0);
	$path_run_inf_wan1 = XNODE_getpathbytarget("/runtime", "inf", "uid", $WAN1, 0);
	$wan1_inet = query($path_inf_wan1."/inet"); 
	$wan1_phyinf = query($path_inf_wan1."/phyinf");
	$wan2_inet = query($path_inf_wan2."/inet");
	$path_wan1_inet = XNODE_getpathbytarget("/inet", "entry", "uid", $wan1_inet, 0);
	$path_wan1_phyinf = XNODE_getpathbytarget("", "phyinf", "uid", $wan1_phyinf, 0);
	$path_wan2_inet = XNODE_getpathbytarget("/inet", "entry", "uid", $wan2_inet, 0); 
	
	$Type="";
	$EthernetType="";
	$Username="";
	$Password="";
	$MaxIdletime=0;
	$ServiceName="";
	$AutoReconnect="false";
	
	if(query($path_run_inf_wan1."/inet/ipv4/valid") == 1)
	{
		$ipaddr=query($path_run_inf_wan1."/inet/ipv4/ipaddr");
		$gateway=query($path_run_inf_wan1."/inet/ipv4/gateway");
		$mask=ipv4int2mask(query($path_run_inf_wan1."/inet/ipv4/mask"));	
		$dns1=query($path_run_inf_wan1."/inet/ipv4/dns");
		$dns2=query($path_run_inf_wan1."/inet/ipv4/dns:2");
	}
	$MTU=1500;
	//$mac=query("/runtime/devdata/wanmac");
	
	$mode=query($path_wan1_inet."/addrtype");
	if($mode == "ppp4" && query($path_wan1_inet."/ppp4/over") == "eth")
	{$PPPoE_IPv4 = "true";}
	
	$Type="ETH";
	
	if($mode == "ipv4")
	{
		anchor($path_wan1_inet."/ipv4");
		if(query("ipv4in6/mode") == "dslite")	//-----DS-Lite
		{
			$EthernetType="DsLite";
			if(query("ipv4in6/remote") != "") //DS-Lite_static
			{	
				$DsLite_Configuration = "Manual";
				$DsLite_AFTR_IPv6Address = query("ipv4in6/remote");
			} 
			else //DS-Lite_dynamic
			{	
				$DsLite_Configuration = "Dhcpv6Option";
				$DsLite_AFTR_IPv6Address = query($path_run_inf_wan1."/inet/ipv4/ipv4in6/remote");
			}
			$DsLite_B4IPv4Address = query("ipaddr");	
		}	
		else if(query("static") == 1) //-----Static     
		{
			$EthernetType="Static";
			$ipaddr=query("ipaddr");
			$gateway=query("gateway");
			$mask=ipv4int2mask(query("mask"));
			$MTU=query("mtu");
			$dns1=query("dns/entry");
			$dns2=query("dns/entry:2");
			if(query($path_wan1_phyinf."/macaddr")!="")
			{
				$mac=query($path_wan1_phyinf."/macaddr");
			}
		}
		else if(query("static") == 0) //-----DHCP
		{
			$EthernetType="DHCP";
			$MTU=query("mtu");
			if(query($path_wan1_phyinf."/macaddr")!="")
			{
				$mac=query($path_wan1_phyinf."/macaddr");
			}
		}
	}
	else if($mode == "ppp10" || $PPPoE_IPv4 == "true") //-----PPPoE
	{
		anchor($path_wan1_inet."/ppp4");
		if(query("static") == 1)
		{
			$EthernetType="StaticPPPoE";
			$ipaddr=query($path_wan1_inet."/ppp4/ipaddr");
		}
		else
		{
			$EthernetType="DHCPPPPoE";
			$ipaddr=query($path_run_inf_wan1."/inet/ppp4/local"); 
		}
		$mask="255.255.255.255";
		$gateway=query($path_run_inf_wan1."/inet/ppp4/peer");
		$dns1=query($path_run_inf_wan1."/inet/ppp4/dns"); 
		$dns2=query($path_run_inf_wan1."/inet/ppp4/dns:2"); 
		$Username=get("x","username"); 
		$Password=get("x","password");
		$MaxIdletime=query("dialup/idletimeout");
		$ServiceName=get("x","pppoe/servicename");  
		if(query($path_wan1_phyinf."/macaddr")!="")
		{
			$mac=query($path_wan1_phyinf."/macaddr");
		}
		if(query("dialup/mode") == "auto")
		{
			$AutoReconnect="true";
		}
		$MTU=query("mtu");
	}
	else if($mode == "ppp4" && query($path_wan1_inet."/ppp4/over") == "pptp")	//-----PPTP
	{
		anchor($path_wan2_inet."/ipv4");
	
		if(query("static") == 1)
		{
			$EthernetType="StaticPPTP";
			$ipaddr=query("ipaddr");
			$gateway=query("gateway");
			$mask=ipv4int2mask(query("mask")); 
			$dns1=query("dns/entry");
			$dns2=query("dns/entry:2");
		}
		else
		{
			$EthernetType="DynamicPPTP";
			$dns1=get("", $path_run_inf_wan1."/inet/ppp4/dns");
			$dns2=get("", $path_run_inf_wan1."/inet/ppp4/dns:2");
		}
		$Username=get("x",$path_wan1_inet."/ppp4/username");
		$Password=get("x",$path_wan1_inet."/ppp4/password");
		$MaxIdletime=query($path_wan1_inet."/ppp4/dialup/idletimeout");
		$ServiceName=get("x",$path_wan1_inet."/ppp4/pptp/server");    
		if(query($path_wan1_phyinf."/macaddr")!="")
		{
			$mac=query($path_wan1_phyinf."/macaddr");
		}
		if(query($path_wan1_inet."/ppp4/dialup/mode") == "auto")
		{
			$AutoReconnect="true";
		}
		$MTU=query($path_wan1_inet."/ppp4/mtu");

		$VPNIPAddress = get("", $path_run_inf_wan1."/inet/ppp4/local");
		$VPNSubnetMask = "255.255.255.255";
		$VPNGateway = get("", $path_run_inf_wan1."/inet/ppp4/peer");
	}
	else if($mode == "ppp4" && query($path_wan1_inet."/ppp4/over") == "l2tp")	//-----L2TP
	{
		anchor($path_wan2_inet."/ipv4");
		if(query("static") == 1)
		{
			$EthernetType="StaticL2TP";
			$ipaddr=query("ipaddr");		
			$gateway=query("gateway");
			$mask=ipv4int2mask(query("mask"));
			$dns1=query("dns/entry");
			$dns2=query("dns/entry:2");
		}
		else
		{
			$EthernetType="DynamicL2TP";
			$dns1=get("", $path_run_inf_wan1."/inet/ppp4/dns");
			$dns2=get("", $path_run_inf_wan1."/inet/ppp4/dns:2");
		}
		$Username=get("x",$path_wan1_inet."/ppp4/username");
		$Password=get("x",$path_wan1_inet."/ppp4/password");
		$MaxIdletime=query($path_wan1_inet."/ppp4/dialup/idletimeout");
		$ServiceName=get("x",$path_wan1_inet."/ppp4/l2tp/server");
		if(query($path_wan1_phyinf."/macaddr")!="")
		{
			$mac=query($path_wan1_phyinf."/macaddr");
		}
		if(query($path_wan1_inet."/ppp4/dialup/mode") == "auto")
		{
			$AutoReconnect="true";
		}
		$MTU=query($path_wan1_inet."/ppp4/mtu");

		$VPNIPAddress = get("", $path_run_inf_wan1."/inet/ppp4/local");
		$VPNSubnetMask = "255.255.255.255";
		$VPNGateway = get("", $path_run_inf_wan1."/inet/ppp4/peer");
	}	
	
	if(query("/advdns/enable") == 1)
	{
		$adv_dns_enable="true";
	}
	else
	{
		$adv_dns_enable="false";
	}
}
else if($active_wan==$WAN3) /* for 3g */
{
	$Type="USB3G";
	
	foreach("/runtime/internetprofile/entry")
	{
		$type = query("type");
		if($type==$Type)
		{
			$active = query("active");
			if($active==1)
			{
				$profileuid = query("profileuid");
				break;
			}
		}
	}
	
	
	if($profileuid=="PRO-3GAUTO")
	{
		$path_profile = "/runtime/auto_config";
		$DialNo = query($path_profile."/dialno");
		$APN = query($path_profile."/apn");
	}
	else
	{
		$path_profile = XNODE_getpathbytarget("/internetprofile", "entry", "uid", $profileuid, 0);
		$DialNo = query($path_profile."/config/dialno");
		$APN = query($path_profile."/config/apn");
	}
	
	$path_inf_wan3 = XNODE_getpathbytarget("", "inf", "uid", $WAN3, 0);
	$path_run_inf_wan3 = XNODE_getpathbytarget("/runtime", "inf", "uid", $WAN3, 0);
	$wan3_phyinf = query($path_inf_wan3."/phyinf");
	$path_wan3_phyinf = XNODE_getpathbytarget("", "phyinf", "uid", $wan3_phyinf, 0);
	
	if(query($path_run_inf_wan3."/inet/ppp4/valid") == 1)
	{
		$ipaddr=query($path_run_inf_wan3."/inet/ppp4/local");
		$gateway=query($path_run_inf_wan3."/inet/ppp4/peer");
		$mask="255.255.255.255";
		$dns1=query($path_run_inf_wan3."/inet/ppp4/dns");
		$dns2=query($path_run_inf_wan3."/inet/ppp4/dns:2");
	}
	
	if(query($path_wan3_phyinf."/macaddr")!="")
	{
		$mac=query($path_wan3_phyinf."/macaddr");
	}
}
else if($active_wan==$WAN7) /* for wisp */
{
	$Type="WISP";
	
	foreach("/runtime/internetprofile/entry")
	{
		$type = query("type");
		if($type==$Type)
		{
			$active = query("active");
			if($active==1)
			{
				$profileuid = query("profileuid");
				break;
			}
		}
	}
	
	$path_profile = XNODE_getpathbytarget("/internetprofile", "entry", "uid", $profileuid, 0);
	$HotspotName = query($path_profile."/profilename");
	$encrtype = query($path_profile."/config/encrtype");
	
	if($encrtype=="NONE")
	{
		$Password = "None";
	}
	else if($encrtype=="WEP")
	{
		$Password = query($path_profile."/config/wep/key");
	}
	else if($encrtype=="TKIP" || $encrtype=="AES" || $encrtype=="TKIP+AES")
	{
		$Password = query($path_profile."/config/psk/key");
	}
	
	$path_inf_wan7 = XNODE_getpathbytarget("", "inf", "uid", $WAN7, 0);
	$wan7_phyinf = query($path_inf_wan7."/phyinf");
	$path_run_inf_wan7 = XNODE_getpathbytarget("/runtime", "inf", "uid", $WAN7, 0);
	$path_run_phyinf_wan7 = XNODE_getpathbytarget("/runtime", "phyinf", "uid", $wan7_phyinf, 0);
	
	if(query($path_run_inf_wan7."/inet/ipv4/valid") == 1)
	{
		$ipaddr=query($path_run_inf_wan7."/inet/ipv4/ipaddr");
		$gateway=query($path_run_inf_wan7."/inet/ipv4/gateway");
		$mask=ipv4int2mask(query($path_run_inf_wan7."/inet/ipv4/mask"));	
		$dns1=query($path_run_inf_wan7."/inet/ipv4/dns");
		$dns2=query($path_run_inf_wan7."/inet/ipv4/dns:2");
	}
	
	if(query($path_run_phyinf_wan7."/macaddr")!="")
	{
		$mac=query($path_run_phyinf_wan7."/macaddr");
	}
}

if($ipaddr=="0.0.0.0")
{
	$ipaddr="";
}
if($mask=="0.0.0.0")
{
	$mask="";
}
if($gateway=="0.0.0.0")
{
	$gateway="";
}
if($dns1=="0.0.0.0")
{
	$dns1="";
}
if($dns2=="0.0.0.0")
{
	$dns2="";
}

?>
<soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
	<soap:Body>
		<GetWanSettingsResponse xmlns="http://purenetworks.com/HNAP1/">
			<GetWanSettingsResult>OK</GetWanSettingsResult>
			<Type><?=$Type?></Type>
			<EthernetType><?=$EthernetType?></EthernetType>
			<Username><? echo escape("x",$Username); ?></Username>
			<Password><? echo escape("x",$Password); ?></Password>
			<MaxIdleTime><?=$MaxIdletime?></MaxIdleTime>
			<HostName><? echo get("x", "/device/hostname");?></HostName>
			<VPNIPAddress><?=$VPNIPAddress?></VPNIPAddress>
			<VPNSubnetMask><?=$VPNSubnetMask?></VPNSubnetMask>
			<VPNGateway><?=$VPNGateway?></VPNGateway>
			<ServiceName><? echo escape("x",$ServiceName); ?></ServiceName>
			<AutoReconnect><?=$AutoReconnect?></AutoReconnect>
			<IPAddress><?=$ipaddr?></IPAddress>
			<SubnetMask><?=$mask?></SubnetMask>
			<Gateway><?=$gateway?></Gateway>
			<DNS>
				<Primary><?=$dns1?></Primary>
				<Secondary><?=$dns2?></Secondary>
			</DNS>
			<OpenDNS>
				<enable><?=$adv_dns_enable?></enable>
			</OpenDNS>
			<MacAddress><?=$mac?></MacAddress>
			<MTU><?=$MTU?></MTU>
			<HotspotName><?=$HotspotName?></HotspotName>
			<DialNo><?=$DialNo?></DialNo>
			<APN><?=$APN?></APN>
		</GetWanSettingsResponse>
	</soap:Body>
</soap:Envelope>
