<?
include "/htdocs/phplib/xnode.php";
include "/htdocs/phplib/trace.php";
include "/etc/services/WIFI/function.php";
echo "#!/bin/sh\n";

//----------------------------------ACL setting------------------------------------------------------------------//
function aclmode($uid)
{
	$dev = devname($uid);
	// We use the /acl/macctrl to replace wifi mac filter db path.
	/*
	$acl_count	= query($wifi1."/acl/count");
	$acl_max	= query($wifi1."/acl/max");
	$acl_policy	= query($wifi1."/acl/policy");
	*/
	$acl_count	= query("/acl/macctrl/count");
	$acl_max	= query("/acl/macctrl/max");
	$acl_policy	= query("/acl/macctrl/policy");
	
	echo 'ifconfig '.$dev.' down\n';
	echo 'iwpriv '.$dev.' set_mib aclnum=0\n';
	
	if($acl_policy=="ACCEPT")		{$ACLMODE=2;}
	else if ($acl_policy=="DROP")	{$ACLMODE=1;}
	else							{$ACLMODE=0;}
	echo 'iwpriv '.$dev.' set_mib aclmode='.$ACLMODE.'\n';
	// We use the /acl/macctrl to replace wifi mac filter db path.
	//foreach ($wifi1."/acl/entry")
	foreach ("/acl/macctrl/entry")
	{
		if ($InDeX > $acl_count || $InDeX > $acl_max) break;
		$acl_enable = query("enable");
		if ($acl_enable == 1)
		{
			$acl_list = query("mac");
			$a = cut($acl_list, "0", ":");
			$a = $a.cut($acl_list, "1", ":");
			$a = $a.cut($acl_list, "2", ":");
			$a = $a.cut($acl_list, "3", ":");
			$a = $a.cut($acl_list, "4", ":");
			$a = $a.cut($acl_list, "5", ":");
			echo 'iwpriv '.$dev.' set_mib acladdr='.$a.'\n';
		}
	}
	echo 'ifconfig '.$dev.' up\n';
}
//----------------------------------ACL setting END------------------------------------------------------------------//
	
	
$uid1 = "BAND24G-1.1";
$uid2 = "BAND5G-1.1";
$uid3 = "BAND24G-1.2";
$uid4 = "BAND5G-1.2";

if (isfile("/var/run/BAND24G-1.1.UP") == 1)	{aclmode($uid1);}
if (isfile("/var/run/BAND5G-1.1.UP") == 1)	{aclmode($uid2);}
if (isfile("/var/run/BAND24G-1.2.UP") == 1)	{aclmode($uid3);}
if (isfile("/var/run/BAND5G-1.2.UP") == 1)	{aclmode($uid4);}

echo "exit 0\n";

?>
