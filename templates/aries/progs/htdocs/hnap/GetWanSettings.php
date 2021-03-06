HTTP/1.1 200 OK
Content-Type: text/xml; charset=utf-8

<?
echo "\<\?xml version='1.0' encoding='utf-8'\?\>";
include "/htdocs/phplib/xnode.php";
include "/htdocs/webinc/config.php";
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
$Username="";
$Password="";
$MaxIdletime=0;
$ServiceName="";
$AutoReconnect="true";
if(query($path_run_inf_wan1."/inet/ipv4/valid") == 1)
{
	$ipaddr=query($path_run_inf_wan1."/inet/ipv4/ipaddr");
	$gateway=query($path_run_inf_wan1."/inet/ipv4/gateway");
	$mask=ipv4int2mask(query($path_run_inf_wan1."/inet/ipv4/mask"));	
	$dns1=query($path_run_inf_wan1."/inet/ipv4/dns");
	$dns2=query($path_run_inf_wan1."/inet/ipv4/dns:2");
}
$MTU=1500;
$mac=query("/runtime/devdata/wanmac");

$mode=query($path_wan1_inet."/addrtype");
if($mode == "ppp4" && query($path_wan1_inet."/ppp4/over") == "eth")
{$PPPoE_IPv4 = "true";}
if($mode == "ipv4")
{
	anchor($path_wan1_inet."/ipv4");
	if(query("ipv4in6/mode") == "dslite")	//-----DS-Lite
	{
		$Type="DsLite";
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
		$Type="Static";
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
		$Type="DHCP";
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
		$Type="StaticPPPoE";
		$ipaddr=query($path_wan1_inet."/ppp4/ipaddr");
	}
	else
	{
		$Type="DHCPPPPoE";
		$ipaddr=query($path_run_inf_wan1."/inet/ppp4/local"); 
	}
	$mask="255.255.255.255";
	$gateway=query($path_run_inf_wan1."/inet/ppp4/peer");
	//$dns1=query($path_run_inf_wan1."/inet/ppp4/dns"); 
	//$dns2=query($path_run_inf_wan1."/inet/ppp4/dns:2");

	if(query($path_wan1_inet."/ppp4/dns/count") > 0)
	{
		$dns1=query($path_wan1_inet."/ppp4/dns/entry:1"); 
        	$dns2=query($path_wan1_inet."/ppp4/dns/entry:2");
	}
	else
	{
		$dns1="";
		$dns2="";
	}
 
	$Username=get("x","username"); 
	$Password=get("x","password");
	$MaxIdletime=query("dialup/idletimeout");
	$ServiceName=get("x","pppoe/servicename");  
	if(query($path_wan1_phyinf."/macaddr")!="")
	{
		$mac=query($path_wan1_phyinf."/macaddr");
	}
	if(query("dialup/mode") == "manual")
	{
		$AutoReconnect="false";
	}else if(query("dialup/mode") == "auto"){
		$MaxIdletime=0;
	}else if(query("dialup/mode") == "" && query("dialup/idletimeout") == ""){
		$MaxIdletime=5;
	}
	$MTU=query("mtu");
}
else if($mode == "ppp4" && query($path_wan1_inet."/ppp4/over") == "pptp")	//-----PPTP
{
	anchor($path_wan2_inet."/ipv4");

	if(query("static") == 1)
	{
		$Type="StaticPPTP";
		$ipaddr=query("ipaddr");
		$gateway=query("gateway");
		$mask=ipv4int2mask(query("mask")); 
		$dns1=query("dns/entry");
		$dns2=query("dns/entry:2");
	}
	else
	{
		$Type="DynamicPPTP";
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
	if(query($path_wan1_inet."/ppp4/dialup/mode") == "manual")
	{
		$AutoReconnect="false";
        }else if(query($path_wan1_inet."/ppp4/dialup/mode") == "auto"){
                $MaxIdletime=0;
        }else if(query($path_wan1_inet."/ppp4/dialup/mode") == "" && query($path_wan1_inet."/ppp4/dialup/idletimeout") == ""){
                $MaxIdletime=5;
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
		$Type="StaticL2TP";
		$ipaddr=query("ipaddr");		
		$gateway=query("gateway");
		$mask=ipv4int2mask(query("mask"));
		$dns1=query("dns/entry");
		$dns2=query("dns/entry:2");
	}
	else
	{
		$Type="DynamicL2TP";
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
	if(query($path_wan1_inet."/ppp4/dialup/mode") == "manual")
	{
		$AutoReconnect="false";
        }else if(query($path_wan1_inet."/ppp4/dialup/mode") == "auto"){
                $MaxIdletime=0;
        }else if(query($path_wan1_inet."/ppp4/dialup/mode") == "" && query($path_wan1_inet."/ppp4/dialup/idletimeout") == ""){
                $MaxIdletime=5;
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
		</GetWanSettingsResponse>
	</soap:Body>
</soap:Envelope>
