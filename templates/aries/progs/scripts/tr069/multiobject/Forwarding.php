<?
include "/etc/scripts/tr069/phplib/io.php";
include "/etc/scripts/tr069/phplib/api.php";

if($NAME != "Forwarding") return;

$BASE = $TR069_MULTI_BASE."/".$NAME;
$BASE_ENTRY = $BASE."/entry:".$INDEX0;
if($ACTION == "DEL"){
	$P_UID = query($TOP_PATH."/UID");
	$UID = query($BASE_ENTRY."/UID");
	TR069_DeleteObject(1,$NAME,$UID,$P_UID);
	
	$db_base_path = "/route/static";
	$db_path = XNODE_getpathbytarget($db_base_path, "entry", "uid", $UID, "0");

	if ($db_path != "")
	{
		del ($db_path);
		$cnt = get ("", $db_base_path."/count");
		set ($db_base_path."/count", $cnt - 1);
		
		fwrite("w", $EXEC_SHELL_FILE, "#!/bin/sh \n"); 
		fwrite("a", $EXEC_SHELL_FILE, "service ROUTE.STATIC restart \n"); 
	}
}
else if($ACTION == "GET_INDEX"){
	$entry_index = 0;
	$instance_num = $INSTANCE;
	
	foreach ( $BASE."/entry") {
		$tr069_index = getattr("",$TR069_INDEX_STRING);
		if($tr069_index == $instance_num){
			$entry_index = $InDeX;
			break;
		}
	}
	output($entry_index);
}
else{
	if($PARAM_NAME == $TR069_COUNT_STRING){
		$NODE_PATH = $BASE."/".$TR069_COUNT_STRING;
		
		if($ACTION == "GET") {
			if(query($NODE_PATH) == "") {
				set($NODE_PATH,0);
				output(get("", "/route/static/entry#"));
			}
			else
				output(query($NODE_PATH));
		}
		else if($ACTION == "SET") set($NODE_PATH,$SET_VALUE);
		else if($ACTION == "GET_PATH") output($NODE_PATH);
	}
	else if($PARAM_NAME == $TR069_SEQ_STRING){
		$NODE_PATH = $BASE;
		if($ACTION == "GET")
			output(getattr($NODE_PATH,$TR069_SEQ_STRING));
		else if($ACTION == "SET")
			setattr($NODE_PATH,$TR069_SEQ_STRING,$SET_VALUE);
	}
	else if($PARAM_NAME == $TR069_INDEX_STRING){
		$NODE_PATH = $BASE_ENTRY;
		if($ACTION == "GET")
			output(getattr($NODE_PATH,$TR069_INDEX_STRING));
		else if($ACTION == "SET"){
			setattr($NODE_PATH,$TR069_INDEX_STRING,$SET_VALUE);
			$SRT_UID = "SRT-".$SET_VALUE;
			TR069_AddObject(1,$NAME,$SRT_UID,"");
			
			XNODE_getpathbytarget("/route/static", "entry", "uid", $SRT_UID, "1");
			$seqno = get ("", "/route/static/seqno");
			set ("/route/static/seqno", $seqno+1);
			$count = get ("", "/route/static/count");
			set ("/route/static/count", $count+1);
			// Does not needs to restart service. because there will use set.
		}
	}
	else if($PARAM_NAME == $TR069_PATH_STRING){
		$NODE_PATH = $BASE_ENTRY;
		if($ACTION == "GET") output(getattr($NODE_PATH,$TR069_PATH_STRING));
		else if($ACTION == "SET") setattr($NODE_PATH,$TR069_PATH_STRING,$SET_VALUE);
	}
	else{
		$NEXT_PARAM_NAME = cut($PARAM_NAME,1,".");
		
		$NODE_PATH = $BASE_ENTRY;
		$route_uid = get("", $NODE_PATH."/UID");
		$db_path = XNODE_getpathbytarget("/route/static", "entry", "uid", $route_uid, "0");
		if ($db_path == "")	// error checking.
		{ TRACE_error("CANNOT find static routing with UID=".$route_uid); }
		
		if ($NEXT_PARAM_NAME == "Enable")
		{ exec_by_type($ACTION, $db_path."/enable", $SET_VALUE); }
		else if ($NEXT_PARAM_NAME == "Status")
		{
			if ($ACTION == "GET")
			{
				$enable = get("", $db_path."/enable");
				if ($enable == "1")
				{ output ("Enabled"); }
				else if ($enable == "0")
				{ output ("Disabled"); }
			}
			if ($ACTION == "GET_PATH")
			{ output ($db_path."/enable"); }
		}
		else if ($NEXT_PARAM_NAME == "Type")
		{
			$mask = get("", $db_path."/mask");
			if ($ACTION == "GET_PATH")
			{ output ($db_path."/mask"); }
			else if ($ACTION == "GET")
			{
				if ($mask == "32")
				{ output("Host");}
				else
				{ output ("Network"); }
			}
			else if ($ACTION == "SET")
			{
				if ($SET_VALUE == "Host")	// Only set host needs to set the netmask to 32. others does not need to do this.
				{ set($db_path."/mask", "32"); }
			}
		}
		else if ($NEXT_PARAM_NAME == "DestIPAddress")
		{ exec_by_type($ACTION, $db_path."/network", $SET_VALUE); }
		else if ($NEXT_PARAM_NAME == "DestSubnetMask")
		{ 
			$path = $db_path."/mask";
			if ($ACTION == "GET_PATH")
			{ output ($path); }
			else if ($ACTION == "GET")
			{
				$mask = get ("", $path);
				output (ipv4int2mask($mask));
			}
			else if ($ACTION == "SET")
			{ set ($path, ipv4mask2int($SET_VALUE)); }
		}
		else if ($NEXT_PARAM_NAME == "SourceIPAddress")	// not support yet.
		{ TRACE_error("Not Support yet. $PARAM_NAME=".$PARAM_NAME); }
		else if ($NEXT_PARAM_NAME == "SourceSubnetMask")
		{ TRACE_error("Not Support yet. $PARAM_NAME=".$PARAM_NAME); }
		else if ($NEXT_PARAM_NAME == "ForwardingPolicy")
		{ TRACE_error("Not Support yet. $PARAM_NAME=".$PARAM_NAME); }
		else if ($NEXT_PARAM_NAME == "GatewayIPAddress")
		{ exec_by_type($ACTION, $db_path."/via", $SET_VALUE); }
		else if ($NEXT_PARAM_NAME == "Interface")
		{
			if ($ACTION == "GET_PATH")
			{ output ($db_path."/inf"); }
			else if ($ACTION == "GET")	// Todo: Maybe there is a better way to reply.
			{
				$wan_uid = get ("", $db_path."/inf");
				$inf_r_p = XNODE_getpathbytarget("/runtime", "inf", "uid", $wan_uid, "0");
				$addrtype = get ("", $inf_r_p."/inet/addrtype");
				if ($addrtype == "ipv4")
				{ output("InternetGatewayDevice.WANDevice.1.WANConnectionDevice.1.WANIPConnection.1."); }
				else if ($addrtype == "ppp4" || $addrtype == "ppp10")
				{ output("InternetGatewayDevice.WANDevice.1.WANConnectionDevice.1.WANPPPConnection.1."); }
			}
			else if ($ACTION == "SET")
			{
				$wan_index = cut($SET_VALUE,2,".");
				$wan_uid = get("",$TR069_MULTI_BASE."/WANDevice/entry:".$wan_index."/UID");
				set($db_path."/inf",$wan_uid);
			}
		}
		else if ($NEXT_PARAM_NAME == "ForwardingMetric")
		{ exec_by_type($ACTION, $db_path."/metric", $SET_VALUE); }
		else if ($NEXT_PARAM_NAME == "MTU")
		{
			if ($ACTION == "$GET_PATH" || $ACTION == "GET")	// Not support set in this item.
			{
				$inf = get($db_path."/inf");
				$inf_r_p = XNODE_getpathbytarget("/runtime", "inf", "uid", $inf, "0");
				exec_by_type($ACTION, $inf_r_p."/inet/ipv4/mtu", $SET_VALUE);
			}
		}
		else
		{ TRACE_error("Not implemented yet. $PARAM_NAME=".$PARAM_NAME); }
		
		if ($ACTION == "SET")
		{
			fwrite("w", $EXEC_SHELL_FILE, "#!/bin/sh \n"); 
			fwrite("a", $EXEC_SHELL_FILE, "service ROUTE.STATIC restart \n"); 
		}
	}
}
?>