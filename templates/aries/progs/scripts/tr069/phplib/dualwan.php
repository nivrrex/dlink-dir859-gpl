<?
include "/htdocs/phplib/trace.php";
include "/htdocs/webinc/config.php";

$RUNTIME_UID = $WAN1;
foreach("/runtime/inf") {
	$uid_wan = query("uid");
	if(strstr($uid_wan, "WAN-") != "")//should be WAN-1 to 3
	{
		if(query("inet/addrtype")=="ipv4")
		{
			if(query("inet/ipv4/ipaddr")!="")
			{
				$RUNTIME_UID = $uid_wan;
				break;
			}
		}
	}
}

?>