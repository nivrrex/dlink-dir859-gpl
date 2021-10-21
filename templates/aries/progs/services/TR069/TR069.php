<?
include "/etc/scripts/tr069/phplib/env.php";
include "/etc/scripts/tr069/phplib/dualwan.php";
include "/htdocs/phplib/xnode.php";

function startcmd($cmd)	{fwrite(a,$_GLOBALS["START"], $cmd."\n");}
function stopcmd($cmd)	{fwrite(a,$_GLOBALS["STOP"], $cmd."\n");}
function error($errno)	{startcmd("exit ".$errno); stopcmd( "exit ".$errno);}

fwrite("w",$START, "#!/bin/sh\n");
fwrite("w",$STOP,  "#!/bin/sh\n");

//Stop first
stopcmd("killall tr069c");

startcmd("xmldbc -s /runtime/tr069/dbg_level 6");
$LAYOUT = query("/device/layout");
if($LAYOUT == "router"){

	if(query("/tr069/external/enable_dualwan")=="1")
		$UID = $RUNTIME_UID;
	else
		$UID = "WAN-1";
		
	$path_inf_wan = XNODE_getpathbytarget("", "inf", "uid", $UID, 0);
	$wan_inet = query($path_inf_wan."/inet");
	$path_wan_inet = XNODE_getpathbytarget("/inet", "entry", "uid", $wan_inet, 0);
	$mode=query($path_wan_inet."/addrtype");
	$from_DHCP_OPT = 0;
	
	//clear first
	set($RUNTIME_TRBASE."/dhcp_opt/provisioningcode","");
	set($RUNTIME_TRBASE."/dhcp_opt/url","");
	
	if($mode == "ipv4"){
		if(query($path_wan_inet."/ipv4/static") == 0){ //DHCP
			$run_inf = XNODE_getpathbytarget("/runtime", "inf", "uid", $UID, 0);
			set($RUNTIME_TRBASE."/dhcp_opt/provisioningcode",query($run_inf."/udhcpc/tr069_provisioning_code"));
			set($RUNTIME_TRBASE."/dhcp_opt/url",query($run_inf."/udhcpc/tr069_acs_url"));
			//DHCP first
			$acs_url = query($RUNTIME_TRBASE."/dhcp_opt/url");
			if($acs_url != ""){
				$from_DHCP_OPT = 1;
				$provisioningcode = query($RUNTIME_TRBASE."/dhcp_opt/provisioningcode");
			}
		}
	}
	
	if($from_DHCP_OPT == 0){
		//ACS URL
		$acs_url = query($TRBASE."/managementserver/weburl");
		if($acs_url == "") $acs_url = query($TRBASE."/managementserver/defurl");
	}
	
	//ACS Username
	$acs_username = query($TRBASE."/managementserver/webusername");
	if($acs_username == "") $acs_username = query($TRBASE."/managementserver/defusername");
	
	//ACS Password
	$acs_password = query($TRBASE."/managementserver/webpassword");
	if($acs_password == "") $acs_password = query($TRBASE."/managementserver/defpassword");
	
	set($TRBASE."/managementserver/username",$acs_username);
	set($TRBASE."/managementserver/password",$acs_password);
	set($TRBASE."/deviceinfo/provisioningcode",$provisioningcode);
	set($TRBASE."/managementserver/url",$acs_url);
	startcmd("tr069c -d 1 -w ".$UID." &");
}
else if	($LAYOUT == "bridge"){
	startcmd("tr069c -d 1 -l BRIDGE-1 &");
}
?>