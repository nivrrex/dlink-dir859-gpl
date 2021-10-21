<?
include "/htdocs/phplib/trace.php";
include "/htdocs/phplib/xnode.php";

if($_GLOBALS["STATE"] != "")
{
	if($_GLOBALS["STATE"] == "WPS_NONE")
	{
		if(query("/runtime/wps_sta/reason") == "") 
		{
			set("/runtime/wps_sta/state",$_GLOBALS["STATE"]);
		}
	}
	else 
	{
		$done = 0;
		if($_GLOBALS["STATE"] == "WPS_SUCCESS") {$done = 1;}
		set("/runtime/wps_sta/done",$done);
		set("/runtime/wps_sta/state",$_GLOBALS["STATE"]);
		set("/runtime/wps_sta/reason",$_GLOBALS["REASON"]);
	}
}
else
{
	TRACE_error('wps state: $_GLOBALS["STATE"] is empty.');
}
?>
