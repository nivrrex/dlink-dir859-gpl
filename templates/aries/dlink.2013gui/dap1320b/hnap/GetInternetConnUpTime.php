HTTP/1.1 200 OK
Content-Type: text/xml; charset=utf-8

<? 
echo "\<\?xml version='1.0' encoding='utf-8'\?\>";

include "/htdocs/phplib/xnode.php";
include "/htdocs/webinc/config.php";
include "/htdocs/phplib/trace.php"; 

$result = "OK";

$path_wan1  = XNODE_getpathbytarget("", "inf", "uid", $WAN1, 0);
$wan1_phy   = get("x", $path_wan1."/phyinf");
$rwan1_phy  = XNODE_getpathbytarget("/runtime", "phyinf", "uid", $wan1_phy, 0);
$rwan1_inet = XNODE_getpathbytarget("/runtime", "inf", "uid", $WAN1, 0);


$wancable_status    = 0;
$wan_network_status = 0;
$linkstatus = get("x", $rwan1_phy."/linkstatus");
if($linkstatus != "" && $linkstatus != "0") 
{ 
	$wancable_status    = 1;
	$wan_network_status = 1; 
}

$system_uptime = get("x", "/runtime/device/uptime");
$wan_uptime    = get("x", $rwan1_inet."/inet/uptime");

$wan_delta_uptime = $system_uptime - $wan_uptime;
//TRACE_debug("$wan_delta_uptime=".$wan_delta_uptime);

if($wancable_status == 1 && $wan_delta_uptime > 0 &&  $wan_uptime > 0)
{
	$uptime = $wan_delta_uptime;
}
else { $uptime = 0; }


?>
<soap:Envelope 
xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" 
xmlns:xsd="http://www.w3.org/2001/XMLSchema" 
xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/"> 
	<soap:Body>
		<GetInternetConnUpTimeResponse xmlns="http://purenetworks.com/HNAP1/">
			<GetInternetConnUpTimeResult><?=$result?></GetInternetConnUpTimeResult>
			<UpTime><?=$uptime?></UpTime>
		</GetInternetConnUpTimeResponse>
	</soap:Body>
</soap:Envelope>