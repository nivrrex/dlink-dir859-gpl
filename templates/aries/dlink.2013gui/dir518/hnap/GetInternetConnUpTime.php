HTTP/1.1 200 OK
Content-Type: text/xml; charset=utf-8

<? 
echo "\<\?xml version='1.0' encoding='utf-8'\?\>";
include "/htdocs/phplib/xnode.php";
include "/htdocs/webinc/config.php";
include "/htdocs/phplib/trace.php"; 

$result = "OK";

/* Get active profile */
$active_profile_path = XNODE_getpathbytarget("/runtime/internetprofile", "entry", "active", "1", 0);
$active_profile_uid = get("",$active_profile_path."/profileuid");
$active_profile_type =  get("",$active_profile_path."/type");

$wan_network_status = 0;

if ($active_profile_type == "DHCP" || $active_profile_type == "STATIC" || $active_profile_type == "PPPoE")
{
	$path_wan = XNODE_getpathbytarget("", "inf", "uid", $WAN1, 0);
	$wan_inetuid = get("x", $path_wan."/inet");
	$runtime_wan_inet =  XNODE_getpathbytarget("/runtime", "inf", "inet/uid", $wan_inetuid, 0);
	
	$wan_phyuid = get("x", $path_wan."/phyinf");
	$runtime_wan_phy =  XNODE_getpathbytarget("/runtime", "phyinf", "uid", $wan_phyuid, 0);
	$linkstatus = get("x", $runtime_wan_phy."/linkstatus");
	
	$wancable_status = 0;
	if($linkstatus != "") { $wancable_status = 1; }
	if($wancable_status == 1) { $wan_network_status = 1; }
	else { $wan_network_status = 0; }
}
else if ($active_profile_type == "USB3G")
{
	$path_wan = XNODE_getpathbytarget("", "inf", "uid", $WAN3, 0);
	$wan_inetuid = get("x", $path_wan."/inet");
	$runtime_wan_inet =  XNODE_getpathbytarget("/runtime", "inf", "inet/uid", $wan_inetuid, 0);
	
	$path_run_inf_wan = XNODE_getpathbytarget("/runtime", "inf", "uid", $WAN3, 0);
	$status = query($path_run_inf_wan."/pppd/status");
	
	if($status=="connected") { $wan_network_status = 1; }
	else { $wan_network_status = 0; }
}
else if ($active_profile_type == "WISP")
{
	$path_wan = XNODE_getpathbytarget("", "inf", "uid", $WAN7, 0);
	$wan_inetuid = get("x", $path_wan."/inet");
	$runtime_wan_inet =  XNODE_getpathbytarget("/runtime", "inf", "inet/uid", $wan_inetuid, 0);
	
	$wan_phyinf = query($path_wan."/phyinf");
	$path_run_wan_phyinf = XNODE_getpathbytarget("/runtime", "phyinf", "uid", $wan_phyinf, 0);
	$wisp_client_cnt = query($path_run_wan_phyinf."/media/clients/entry#");
	
	if($wisp_client_cnt > 0) { $wan_network_status = 1; }
	else { $wan_network_status = 0; }
}

$system_uptime = get("x", "/runtime/device/uptime");
$wan_uptime = get("x", $runtime_wan_inet."/inet/uptime");
$wan_delta_uptime = $system_uptime - $wan_uptime;

if($wan_network_status == 1 && 
	 $wan_delta_uptime > 0 && 
	 $wan_uptime > 0)
{
	$uptime = $wan_delta_uptime;
}
else { $uptime = 0; }

?>
<soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" 
	xmlns:xsd="http://www.w3.org/2001/XMLSchema" 
	xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"> 
	<soap:Body>
		<GetInternetConnUpTimeResponse xmlns="http://purenetworks.com/HNAP1/">
			<GetInternetConnUpTimeResult><?=$result?></GetInternetConnUpTimeResult>
			<UpTime><?=$uptime?></UpTime>
		</GetInternetConnUpTimeResponse>
	</soap:Body>
</soap:Envelope>