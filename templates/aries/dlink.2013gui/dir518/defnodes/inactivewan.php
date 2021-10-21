<?
include "/htdocs/phplib/trace.php";

foreach("/inf")
{
	$uid = query("uid");
	if(strstr($uid, "WAN")!="")
	{
		$active = query("active");
		if($active==1) set("active", 0);
	}
}

?>

