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
		/* for IPv4 only */
		if($active==1 && $name=="")
		{
			$active_wan = $uid;
			break;
		}
	}
}

if($active_wan==$WAN1) /* for ethernet */
{
	$path_inf_wan1 = XNODE_getpathbytarget("", "inf", "uid", $WAN1, 0);
	$wan1_phyinf = query($path_inf_wan1."/phyinf");
	$path_run_phyinf_wan1 = XNODE_getpathbytarget("/runtime", "phyinf", "uid", $wan1_phyinf, 0);
	$status = get("",$path_run_phyinf_wan1."/linkstatus");
	if( $status != "0" && $status != "")
	{ $statusStr = "CONNECTED"; }
	else 
	{ $statusStr = "DISCONNECTED"; }
}
else if($active_wan==$WAN3) /* for 3g */
{
	$path_run_inf_wan3 = XNODE_getpathbytarget("/runtime", "inf", "uid", $WAN3, 0);
	
	$status = query($path_run_inf_wan3."/pppd/status");
	
	if($status=="connected")
	{ $statusStr = "CONNECTED"; }
	else
	{ $statusStr = "DISCONNECTED"; }
	
}
else if($active_wan==$WAN7) /* for wisp */
{
	$path_inf_wan7 = XNODE_getpathbytarget("", "inf", "uid", $WAN7, 0);
	$wan7_phyinf = query($path_inf_wan7."/phyinf");
	$path_run_wan7_phyinf = XNODE_getpathbytarget("/runtime", "phyinf", "uid", $wan7_phyinf, 0);
	$wisp_client_cnt = query($path_run_wan7_phyinf."/media/clients/entry#");
	
	if($wisp_client_cnt > 0)
	{ $statusStr = "CONNECTED"; }
	else
	{ $statusStr = "DISCONNECTED"; }
	
}
else
{
	$statusStr = "DISCONNECTED";
}

?>
<soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
xmlns:xsd="http://www.w3.org/2001/XMLSchema"
xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
<soap:Body>
<GetWanStatusResponse xmlns="http://purenetworks.com/HNAP1/">
<GetWanStatusResult>OK</GetWanStatusResult>	
<Status><?=$statusStr?></Status>
</GetWanStatusResponse>
</soap:Body></soap:Envelope>
