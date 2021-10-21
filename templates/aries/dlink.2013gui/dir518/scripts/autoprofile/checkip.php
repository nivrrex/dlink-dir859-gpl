<?
include "/htdocs/phplib/xnode.php";
include "/htdocs/phplib/trace.php";
include "/htdocs/webinc/config.php";

echo "#!/bin/sh\n";

function pingresult($result, $dst, $r_path)
{
	$type = get("",$r_path."/type");

	if ($type == "STATIC")
	{
	echo "rm -f ".$result."\n";
	echo "ping ".$dst." > ".$result."\n";
	echo 'RES=`cat '.$result.'`\n';
	echo 'if [ "$RES" == "'.$dst.'is alive!" ]; then\n';
	}

	echo 'xmldbc -s '.$r_path.'/pingresult success\n';

	if ($type == "STATIC")
	{
	echo 'elif [ "$RES" == "" ]; then\n';
	echo 'xmldbc -s '.$r_path.'/pingresult timeout\n';
	echo "else\n";
	echo 'xmldbc -s '.$r_path.'/pingresult failed\n';
	echo "fi\n";
	}

}

$r_pro_path = XNODE_getpathbytarget("/runtime", "internetprofile/entry", "profileuid", $PROUID, 0);
$RSLT = "/var/".$PROUID."_result";

if($TYPE=="static")
{
	echo "ip addr add ".$IP."/".$SUBNET." dev ".$INTERFACE."\n";
	
	$DST = $ROUTER." ";
	
	echo 'xmldbc -s '.$r_pro_path.'/status connected\n';
	pingresult($RSLT, $DST, $r_pro_path);

	//echo "rm -f ".$RSLT."\n";
	echo "ip addr del ".$IP."/".$SUBNET." dev ".$INTERFACE."\n";
	echo 'xmldbc -s '.$r_pro_path.'/status disconnected\n';
}
else if($TYPE=="dhcp")
{
	if($ACTION=="bound")
	{
		$mask = ipv4mask2int($SUBNET);
		echo "ip addr add ".$IP."/".$mask." dev ".$INTERFACE."\n";
		
		echo 'xmldbc -s '.$r_pro_path.'/status connected\n';
		pingresult($RSLT, $ROUTER, $r_pro_path);

		echo "rm -f ".$RSLT."\n";
		echo "/etc/scripts/killpid.sh ".$PID."\n";
		echo "ip addr del ".$IP."/".$mask." dev ".$INTERFACE."\n";
	}
	else { echo 'xmldbc -s '.$r_pro_path.'/status disconnected\n'; }
}
else if($TYPE=="pppoe")
{
	$DST = $DST." ";
	pingresult($RSLT, $DST, $r_pro_path);

	echo "rm -f ".$RSLT."\n";

	echo '/etc/scripts/killpid.sh '.$DIALPID.'\n';
	echo '/etc/scripts/killpid.sh '.$PID.'\n';
	echo 'rm -f '.$DIALSH.'\n';
}
else if($TYPE=="3g")
{
	$reslov = fread("", "/etc/ppp/resolv.conf.".$WAN3);
	$nameserver = cut($reslov, 0, "\n");
	$dns = cut($nameserver, 1, " ");
	
	echo "ip route add ".$dns." dev ppp0\n"; //need to get from runtime phyinf

	$dns = $dns." ";
	echo 'xmldbc -s '.$r_pro_path.'/status connected\n';
	pingresult($RSLT, $dns, $r_pro_path);

	echo "rm -f ".$RSLT."\n";
	
	echo "ip route del ".$dns." dev ppp0\n";
	echo '/etc/scripts/killpid.sh '.$DIALPID.'\n';
	echo '/etc/scripts/killpid.sh '.$PID.'\n';
	echo 'rm -f '.$DIALSH.'\n';
	echo 'xmldbc -s '.$r_pro_path.'/status disconnected\n';
}
else if($TYPE=="wisp")
{
	if($ACTION=="bound")
	{
		$mask = ipv4mask2int($SUBNET);
		echo "ip addr add ".$IP."/".$mask." dev ".$INTERFACE."\n";
		
		echo 'xmldbc -s '.$r_pro_path.'/status connected\n';
		pingresult($RSLT, $ROUTER, $r_pro_path);

		echo "rm -f ".$RSLT."\n";
		echo "/etc/scripts/killpid.sh ".$PID."\n";
		echo "ip addr del ".$IP."/".$mask." dev ".$INTERFACE."\n";
	}
	else { echo 'xmldbc -s '.$r_pro_path.'/status disconnected\n'; }
}
echo "exit 0\n";
?>