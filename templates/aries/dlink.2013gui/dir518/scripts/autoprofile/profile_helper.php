<?
include "/htdocs/webinc/config.php";
include "/htdocs/phplib/xnode.php";
include "/htdocs/phplib/phyinf.php";
include "/htdocs/phplib/trace.php";
include "/etc/services/WIFI/function.php";

/*-------------------------------------------------------
 * [$ACTIVEPRO]
 * 		the profile uid which user selected from web UI,
 *		when user select a profile to active, 
 *		we should check the profile first.
 *-------------------------------------------------------
 * [$TYPE] eth, 3g, wisp
 * [$ACTION]
 *	- starttest: 
 *	- stop:
 *	- checkresult: check testing result	
 *-------------------------------------------------------*/

$ETH = $WAN1;
$3G = $WAN3;
$WISP = $WAN7;

/* ethernet */
$eth_p	= XNODE_getpathbytarget("", "inf", "uid", $ETH, 0);
$eth_phyinf	= query($eth_p."/phyinf");
$eth_ifname	= PHYINF_getifname($eth_phyinf);
$eth_inet	= query($eth_p."/inet");
$eth_inet_p = XNODE_getpathbytarget("/inet", "entry", "uid", $eth_inet, 0);
$eth_over = query($eth_inet_p."/ppp4/over");

/* 3g */
$3g_p	= XNODE_getpathbytarget("", "inf", "uid", $3G, 0);
$3g_phyinf	= query($3g_p."/phyinf");
$3g_inet	= query($3g_p."/inet");
$3g_inet_p = XNODE_getpathbytarget("/inet", "entry", "uid", $3g_inet, 0);
$3g_over = query($3g_inet_p."/ppp4/over");

/* wisp */
$wisp_p = XNODE_getpathbytarget("", "inf", "uid", $WISP, 0);
$wisp_phyinf = query($wisp_p."/phyinf");
$wisp_phyinf_path = XNODE_getpathbytarget("", "phyinf", "uid", $wisp_phyinf, 0);
$wisp_wifi = query($wisp_phyinf_path."/wifi");
$wisp_wifi_p = XNODE_getpathbytarget("/wifi", "entry", "uid", $wisp_wifi, 0);

$sitesurvey_5G_e = "/runtime/wifi_tmpnode/sitesurvey_5G/entry";
$sitesurvey_24G_e = "/runtime/wifi_tmpnode/sitesurvey_24G/entry";

$profile_entry = "/internetprofile/entry";
$profile_run_entry = "/runtime/internetprofile/entry";
$detect_hlper = "/etc/scripts/autoprofile/checkip.php";
$detect_result_finish = 1;
$result_best = 10;
$result_best_uid = "";

function detect_static($act, $id, $ifname, $profile, $hlper)
{
	$detect_type = "static";
	$r_p = XNODE_getpathbytarget("/runtime", "internetprofile/entry", "profileuid", $id, 0);
	
	anchor($profile."/config");
	$ipaddr = query("ipaddr");
	$mask = query("mask");
	$gateway = query("gateway");
	$dns_cnt = query("dns/count");
	if($dns_cnt > 0) { $dns1 = query("dns/entry"); }
	if($dns_cnt > 1) { $dns1 = query("dns/entry:2"); }
	$mtu = query("mtu");
	
	if ( $ipaddr == "" || $mask == "" || $gateway == "" )
	{ 
		set($r_p."/status", "disconnected");
		set($r_p."/pingresult", "failed");
		return ; 
	}

	if($act=="starttest")
	{
		echo "phpsh ".$hlper.
		     " TYPE=".$detect_type.
		     " INTERFACE=".$ifname.
		     " IP=".$ipaddr.
		     " SUBNET=".$mask.
		     " ROUTER=".$gateway.
		     " PROUID=".$id."\n";
	}
	else if($act=="stoptest")
	{
		echo "ip addr del ".$ipaddr."/".$mask." dev ".$ifname."\n";
		echo 'xmldbc -s '.$r_p.'/status disconnected\n';
	}
}

function detect_dhcp($act, $id, $ifname, $profile, $hlper)
{
	$detect_type = "dhcp";
	
	$udhcpc_helper = "/var/run/".$id."-test-udhcpc.sh";
	$udhcpc_pid = "/var/run/".$id."-test-udhcpc.pid";
	
	anchor($profile."/config");
	$host = query("hostname");
	
	if($act=="starttest")
	{
		/* Generate the callback script for udhcpc. */
		fwrite(w, $udhcpc_helper,
		'#!/bin/sh\n'.
		'echo [$0]: act=$1 ifname=$interface ip=$ip mask=$subnet gw=$router dns=$dns... > /dev/console\n'.
		'phpsh '.$hlper.
		' TYPE='.$detect_type.
		' ACTION=$1'.
		' INTERFACE=$interface'.
		' IP=$ip'.
		' SUBNET=$subnet'.
		' "ROUTER=$router"'.
		' "DNS=$dns"'.
		' "PROUID='.$id.'"'.
		' "PID='.$udhcpc_pid.'"\n'.
		' xmldbc -k test-udhcpc \n'.
		'exit 0\n'
			);
		$udhcpc_fail = "/var/run/".$id."-test-udhcpc-fail.sh";
		
		fwrite ("w",$udhcpc_fail, 
			'#!/bin/sh\n'.
			'echo [$0]:> /dev/console\n'.
			'/etc/scripts/killpid.sh '.$udhcpc_pid.'\n'.
			'phpsh '.$hlper.
			' TYPE='.$detect_type.
			' "PROUID='.$id.'"\n'.
			'exit 0\n'
			);
		echo "chmod +x ".$udhcpc_fail."\n";

		echo "xmldbc -t 'test-udhcpc:5:".$udhcpc_fail."'\n";
		
		echo "chmod +x ".$udhcpc_helper."\n";
		echo "udhcpc -i ".$ifname." -H ".$host." -p ".$udhcpc_pid." -s ".$udhcpc_helper."\n";
	}
	else if($act=="stoptest")
	{
		echo "/etc/scripts/killpid.sh ".$udhcpc_pid."\n";
	}
}

function pppoptions($id, $inf, $ifname, $over, $profile)
{
	anchor($profile."/config");
	
	$optfile	= "/etc/ppp/options.".$id;
	
	$mtu = query("mtu");
	if($over=="eth")
	{
		if ($mtu=="" || $mtu > 1492) { $mtu = 1492; }
	}
	else if($over=="tty")
	{
		if ($mtu=="" || $mtu > 1500) { $mtu = 1500; }
	}
	
	$user	= get("s", "username");
	$pass	= get("s", "password");

	$idle = query("dialup/idletimeout");
	if($over=="eth") { $mode = "auto"; } /* alwayson */
	
	$static = query("static");
	if ($static==1)	$ipaddr = query("ipaddr");
	else $ipaddr = "";
	
	$auth_proto	= query("authprotocol");
	
	if($over=="tty")
	{
		$infp   = XNODE_getpathbytarget("", "inf", "uid", "LAN-1", 0);
		if (query($infp."/active")==1 && query($infp."/disable")!=1)
		{
			$inet   = query($infp."/inet");
			$inetp  = XNODE_getpathbytarget("/inet", "entry", "uid", $inet, 0);
			$lan1ip = query($inetp."/ipv4/ipaddr");
		}
		$infp   = XNODE_getpathbytarget("", "inf", "uid", "LAN-2", 0);
		if (query($infp."/active")==1 && query($infp."/disable")!=1)
		{
			$inet   = query($infp."/inet");
			$inetp  = XNODE_getpathbytarget("/inet", "entry", "uid", $inet, 0);
			$lan2ip = query($inetp."/ipv4/ipaddr");
		}
	}
	
	fwrite("w", $optfile, "noauth nodeflate nobsdcomp nodetach");
	fwrite("a", $optfile, " noccp\n");
	
	/* convert mtu to number. */
	$mtu = $mtu + 1 - 1;

	/* static options */
	fwrite("a", $optfile, "lcp-echo-failure 3\n");
	fwrite("a", $optfile, "lcp-echo-interval 30\n");
	fwrite("a", $optfile, "lcp-echo-failure-2 14\n");
	fwrite("a", $optfile, "lcp-echo-interval-2 6\n");
	fwrite("a", $optfile, "lcp-timeout-1 10\n");
	fwrite("a", $optfile, "lcp-timeout-2 10\n");
	fwrite("a", $optfile, "ipcp-accept-remote ipcp-accept-local\n");
	fwrite("a", $optfile, "mtu ".$mtu."\n");
	fwrite("a", $optfile, "linkname ".$inf."\n");
	fwrite("a", $optfile, "ipparam ".$inf."\n");
	fwrite("a", $optfile, "usepeerdns\n");	/* always use peer's dns.*/
	
	if ($user!="") fwrite("a",$optfile, 'user "'.$user.'"\n');
	else
	{
		if ($over=="tty")			fwrite("a",$optfile, 'user guest\n');
	}
	
	if ($pass!="") fwrite("a",$optfile, 'password "'.$pass.'"\n');
	else
	{
		if ($over=="tty")			fwrite("a",$optfile, 'password guest\n');
	}
	
	fwrite("a",$optfile, "persist\nmaxfail 1\n");

	/* Set local and remote IP */
	if ($ipaddr=="")
	{
		fwrite("a",$optfile, "noipdefault\n");
	}
	else
	{
		fwrite("a", $optfile, $ipaddr.":10.112.113.".cut($inf, 1, "-")."\n");
		if ($static==1) fwrite("a", $optfile, "ipcp-ignore-local\n");
	}

	if ($over=="eth")
	{
		$service= get("s", "servicename");
		fwrite("a", $optfile, "kpppoe pppoe_device ".$ifname."\n");
		fwrite("a", $optfile, "pppoe_hostuniq\n");
		if ($service!="") fwrite("a", $optfile, "pppoe_srv_name \"".$service."\"\n");
	}
	else if($over=="tty")
	{
		if ($auth_proto!="")
		{
			/* Authentication protocol PAP only */
			if($auth_proto=="PAP")
			{
				fwrite("a", $optfile, "refuse-eap\n");
				fwrite("a", $optfile, "refuse-chap\n");
				fwrite("a", $optfile, "refuse-mschap\n");
				fwrite("a", $optfile, "refuse-mschap-v2\n");
			}
			/* Authentication protocol CHAP only */
			if($auth_proto=="CHAP")
			{
				fwrite("a", $optfile, "refuse-eap\n");
				fwrite("a", $optfile, "refuse-pap\n");
				fwrite("a", $optfile, "refuse-mschap\n");
				fwrite("a", $optfile, "refuse-mschap-v2\n");
			}
		}
		fwrite("a",$optfile, "tty_ppp3g ppp3g_chat /etc/ppp/chat.".$id."-".$inf."\n");
		fwrite("a",$optfile, "modem\n");
		fwrite("a",$optfile, "crtscts\n");
		fwrite("a",$optfile, $ifname."\n");
		fwrite("a",$optfile, "115200\n");
		fwrite("a",$optfile, "novj\n");
		
		if ($lan1ip != "" || $lan2ip != "")
		{
			if($lan1ip != "") $lanip = $lan1ip;
			if($lan2ip != "") $lanip = $lanip.",".$lan2ip;
			fwrite("a",$optfile, "excluded_peer_ip ".$lanip."\n");
		}
	}

	return $optfile;
}

function detect_pppoe($act, $id, $inf, $over, $optfile, $hlper)
{
	if($over=="eth") { $type = "pppoe"; }
	else if($over=="tty") { $type = "3g"; }
	
	$sfile		= "/var/run/ppp-".$inf.".status";
	$pppd_pid	= "/var/run/ppp-".$inf.".pid";
	$dialuppid = "/var/run/ppp-".$id."-dialup.pid";
	$dialupsh	= "/var/run/ppp-".$id."-dialup.sh";
	
	$ipup = "/var/run/wantest/ppp/".$id."-ip-up";
	$ipdown = "/var/run/wantest/ppp/".$id."-ip-down";
	$pppstatus = "/var/run/wantest/ppp/".$id."-ppp-status";
	
	$r_pro_path = XNODE_getpathbytarget("/runtime", "internetprofile/entry", "profileuid", $id, 0);

	if ($over == "eth")
	{
		$pro_path = XNODE_getpathbytarget("/internetprofile", "entry", "uid", $id, 0);
		$user	= get("", $pro_path."/config/username");
		$pass	= get("", $pro_path."/config/password");
		if ($user == "" || $pass == "")
		{ 
			set($r_pro_path."/status", "disconnected");
			set($r_pro_path."/pingresult", "failed");
			return ; 
		}
	}

	if($act=="starttest")
	{
		fwrite("w", $ipup, "#!/bin/sh\n");
		fwrite("a", $ipup, "echo [$0]: ifname[$1] device[$2] speed[$3] ip[$4] remote[$5] param[$6] > /dev/console\n");
		fwrite("a", $ipup, "phpsh /etc/scripts/autoprofile/checkip.php ".
											 " TYPE=".$type.
											 " PROUID=".$id.
											 " PID=".$pppd_pid.
											 " DIALPID=".$dialuppid.
											 " DIALSH=".$dialupsh.
											 " DST=$5\n");
		
		fwrite("w", $ipdown, "#!/bin/sh\n");
		fwrite("a", $ipdown, "echo stop... > /dev/console\n");
		
		fwrite("w", $pppstatus, "#!/bin/sh\n");
		fwrite("a", $pppstatus, 'echo "[$0]: [$1] [$2] [$3] [$4] ['.$id.']" > /dev/console\n');
		
		fwrite("a", $pppstatus, 'xmldbc -s '.$r_pro_path.'/status $2 > /dev/console\n');
		
		/* Dial-up script */
		fwrite("w", $dialupsh, "#!/bin/sh\n");
		fwrite("a", $dialupsh, 'chmod +x '.$ipup.' '.$pppstatus.'\n');
		fwrite("a", $dialupsh, 'cp '.$ipup.' /etc/ppp/ip-up\n');
		fwrite("a", $dialupsh, 'cp '.$ipdown.' /etc/ppp/ip-down\n');
		fwrite("a", $dialupsh, 'cp '.$pppstatus.' /etc/ppp/ppp-status\n');
		fwrite("a", $dialupsh,
			'echo $$ > '.$dialuppid.'\n'.
			'pppd file '.$optfile.'\n');
		fwrite("a", $dialupsh, 'rm -f '.$dialuppid.'\nexit 0\n');
		
		echo 'chmod +x '.$dialupsh.'\n';
		
		if($over=="eth") { echo $dialupsh.' &\n'; }
		else if($over=="tty") { echo $dialupsh.'\n'; }
	}
	else if($act=="stoptest")
	{
		echo '/etc/scripts/killpid.sh '.$dialuppid.'\n';
		echo '/etc/scripts/killpid.sh '.$pppd_pid.'\n';
		echo 'rm -f '.$dialupsh.'\n';
		echo 'xmldbc -s '.$r_pro_path.'/status disconnected\n';
	}
}

function detect_3g($act, $id, $pro_type, $dialno, $apn, $3G, $ttyp, $3g_over, $detect_hlper)
{
	if ($ttyp!="")
	{
		$devname = query($ttyp."/devname");
		$devnum  = query($ttyp."/devnum");
		$vid     = query($ttyp."/vid");
		$pid     = query($ttyp."/pid");
	}
	
	if($act=="starttest")
	{
		echo 'xmldbc -s '.$ttyp.'/apn "'.$apn.'"\n';
		echo 'xmldbc -s '.$ttyp.'/dialno "'.$dialno.'"\n';
		echo 'usb3gkit -o /etc/ppp/chat.'.$id.'-'.$3G.' -v 0x'.$vid.' -p 0x'.$pid.' -d '.$devnum.'\n';
		$3g_ifname = $devname;
		
		$optfile = pppoptions($id, $3G, $3g_ifname, $3g_over, $pro_path);
		detect_pppoe($act, $id, $3G, $3g_over, $optfile, $detect_hlper);
	}
	else if($act=="stoptest")
	{
		/*
		$reslov = fread("", "/etc/ppp/resolv.conf.".$WAN3);
		$nameserver = cut($reslov, 0, "\n");
		$dns = cut($nameserver, 1, " ");
		echo "ip route del ".$dns." dev ppp0\n";
		*/
	}
}

function detect_wisp($act, $id, $ifname, $hlper)
{
	$detect_type = "wisp";
	
	$udhcpc_helper = "/var/run/".$id."-test-udhcpc.sh";
	$udhcpc_pid = "/var/run/".$id."-test-udhcpc.pid";
	
	if($act=="starttest")
	{
		/* Generate the callback script for udhcpc. */
		fwrite(w, $udhcpc_helper,
		'#!/bin/sh\n'.
		'echo [$0]: act=$1 ifname=$interface ip=$ip mask=$subnet gw=$router dns=$dns... > /dev/console\n'.
		'phpsh '.$hlper.
		' TYPE='.$detect_type.
		' ACTION=$1'.
		' INTERFACE=$interface'.
		' IP=$ip'.
		' SUBNET=$subnet'.
		' "ROUTER=$router"'.
		' "DNS=$dns"'.
		' "PROUID='.$id.'"'.
		' "PID='.$udhcpc_pid.'"\n'.
		'exit 0\n'
		);
		
		echo "chmod +x ".$udhcpc_helper."\n";
		echo "udhcpc -i ".$ifname." -H dir518L -p ".$udhcpc_pid." -s ".$udhcpc_helper."\n";
	}
	else if($act=="stoptest")
	{
		echo "/etc/scripts/killpid.sh ".$udhcpc_pid."\n";
	}
}

function phytype($protype)
{
	if($protype=="STATIC" || $protype=="DHCP" || $protype=="PPPoE") { return "eth"; }
	else if($protype=="USB3G") { return "3g"; }
	else if($protype=="WISP") { return "wisp"; }
	
	TRACE_error("unkonw type:[".$protype."]\n");
}

function getlevel($protype)
{
	if($protype=="STATIC") { return "1"; }
	else if($protype=="DHCP") { return "2"; }
	else if($protype=="PPPoE") { return "3"; }
	else if($protype=="USB3G") { return "4"; }
	else if($protype=="WISP") { return "5"; }
	
	TRACE_error("unkonw type:[".$protype."]\n");
}

function clean_result($path, $prouid)
{
	foreach($path)
	{
		$r_prouid = query("profileuid");
		if($r_prouid==$prouid)
		{
			$r_path = $path.":".$InDeX;
			break;
		}
	}
	
	if($r_path!="") { del($r_path); }
}

function get_last_uid($path, $protype)
{
	$lastuid = "";
	foreach($path)
	{
		$type = query("profiletype");
		if($type==$protype)
		{
			$lastuid = query("uid");
		}
	}
	return $lastuid;
}

function get_first_index($path, $protype)
{
	foreach($path)
	{
		$type = query("profiletype");
		if($type==$protype)
		{
			$index = $InDeX;
			break;
		}
	}
	return $index;
}

function get_entry_index($path, $id)
{
	foreach($path)
	{
		$uid = query("uid");
		if($id==$uid)
		{
			$index = $InDeX;
			break;
		}
		else { $index=0; }
	}
	return $index;
}

function set_information($path, $id, $type)
{
	clean_result($path, $id);
	$r_cnt = get("x", $path."#");
	$r_cnt = $r_cnt + 1;
	set($path.":".$r_cnt."/profileuid", $id);
	set($path.":".$r_cnt."/type", $type);
	$level = getlevel($type);
	set($path.":".$r_cnt."/level", $level);
}

$phyinfp = XNODE_getpathbytarget("", "phyinf", "uid", $3g_phyinf, 0);
$slot	= query($phyinfp."/slot");
$ttyp	= XNODE_getpathbytarget("/runtime/tty", "entry", "slot", $slot, 0);

$3g_status_run_path = "/runtime/internetprofile/3gstatus";
$wisp_status_run_path = "/runtime/internetprofile/wispstatus";

if($ACTIVEPRO!="") /* if user active profile from web UI */
{
	$pro_path = XNODE_getpathbytarget("internetprofile", "entry", "uid", $ACTIVEPRO, 0);
	$pro_type = query($pro_path."/profiletype");
	set_information($profile_run_entry, $ACTIVEPRO, $pro_type);
	echo "phpsh /etc/scripts/activeprofile.php PROUID=".$ACTIVEPRO."\n";
}
else
{
	if($ACTION=="starttest" || $ACTION=="stoptest")
	{
			if($TYPE=="eth")
			{
				foreach($profile_entry)
				{
					$uid = query("uid");
					$pro_type = query("profiletype");
					$pro_path = $profile_entry.":".$InDeX;
					
					if (phytype($pro_type)=="eth")
					{
						if($ACTION=="starttest") { set_information($profile_run_entry, $uid, $pro_type); }
						else if ($ACTION=="stoptest") { set($pro_path."/active", "0"); }
					
						if($pro_type=="STATIC")
						{
							detect_static($ACTION, $uid, $eth_ifname, $pro_path, $detect_hlper);
						}
						else if($pro_type=="DHCP")
						{
							detect_dhcp($ACTION, $uid, $eth_ifname, $pro_path, $detect_hlper);
						}
						else if($pro_type=="PPPoE")
						{
							$optfile = pppoptions($uid, $ETH, $eth_ifname, $eth_over, $pro_path);
							detect_pppoe($ACTION, $uid, $ETH, $eth_over, $optfile, $detect_hlper);
						}
					}
				}
			}
			else if($TYPE=="3g")
			{
				$auto_config_path = "/runtime/auto_config";
				$3g_now = query($3g_status_run_path."/now");
				
				if ($ttyp!="")
				{
					if($auto_config_path!="") /* we have auto config for usb3g, do auto config first */
					{
						$uid = "PRO-3GAUTO"; //need to check...
						$pro_type = "USB3G";
						$dialno	= query($auto_config_path."/dialno");
						$apn	= query($auto_config_path."/apn");
					}
					else
					{
						$start_indx = 0;
						$usb3g_lastone = get_last_uid($profile_entry, "USB3G");
						
						$start_indx = 0;
						$start_indx = get_entry_index($profile_entry, $3g_now);
						
						
						
						
						foreach($profile_entry)
						{
							$uid = query("uid");
							$pro_type = query("profiletype");
							
							if($ACTION=="starttest")
							{
								if($uid==$usb3g_lastone) { set($3g_status_run_path."/lastone", 1); }
								else { set($3g_status_run_path."/lastone", 0); }
							}
							
							if($InDeX > $start_indx && $uid!="" && phytype($pro_type)=="3g")
							{
								$dialno	= query("config/dialno");
								$apn	= query("config/apn");
								break;
							}
						}
					}
				}
				else { TRACE_error("usb3g interface is not ready.\n"); }
				
				if($ACTION=="starttest")
				{
					set_information($profile_run_entry, $uid, $pro_type);
					set($3g_status_run_path."/now", $uid);
				}
				
				detect_3g($ACTION, $uid, $pro_type, $dialno, $apn, $3G, $ttyp, $3g_over, $detect_hlper);
			}
			else if($TYPE=="wisp")
			{
				$wisp_now = query($wisp_status_run_path."/now");
				$band = query($wisp_status_run_path."/band");
				$status = query($wisp_status_run_path."/status");
				
				if($band==$WLAN1_REP) { $wisp_ifname = devname($WLAN1_REP); }
				else if($band==$WLAN2_REP) { $wisp_ifname = devname($WLAN2_REP); }
				
				if($wisp_now!="") { $pro_indx = get_entry_index($profile_entry, $wisp_now); }
				else { $pro_indx = get_first_index($profile_entry, "WISP"); }
				$pro_p = $profile_entry.":".$pro_indx;
				
				$uid = query($pro_p."/uid");
				if($ACTION=="starttest") { set_information($profile_run_entry, $uid, "WISP"); }
				
				detect_wisp($ACTION, $uid, $wisp_ifname, $detect_hlper);
			}
	}
	else if($ACTION=="checkresult")
	{
		if($TYPE=="eth")
		{
			foreach($profile_run_entry)
			{
				$pro_type = query("type");
				if(phytype($pro_type)=="eth")
				{
					$status = query("status");
					if($status!="disconnected")
					{
						$detect_result_finish = 0;
						break;
					}
					
					$pingresult = query("pingresult");
					if($pingresult=="success")
					{
						$uid = query("profileuid");
						$level = getlevel($pro_type);
						if($level < $result_best)
						{
							$result_best = $level;
							$result_best_uid = $uid;
						}
					}
				}
			}
		}
		else if($TYPE=="3g")
		{
			$nowtest = query("/runtime/internetprofile/3gstatus/now");
			$nowtest_p = XNODE_getpathbytarget("/runtime", "internetprofile/entry", "profileuid", $nowtest, 0);
			$status = query($nowtest_p."/status");
			if($status!="disconnected")
			{
				$detect_result_finish = 0;
			}
			else
			{
				$detect_result_finish = 1;
				$pingresult = query($nowtest_p."/pingresult");
				if($pingresult=="success")
				{
					$result_best = query($nowtest_p."/level");
					$result_best_uid = query($nowtest_p."/profileuid");
				}
			}
		}
		else if($TYPE=="wisp")
		{
			$nowtest = query("/runtime/internetprofile/wispstatus/now");
			$nowtest_p = XNODE_getpathbytarget("/runtime", "internetprofile/entry", "profileuid", $nowtest, 0);
			$status = query($nowtest_p."/status");
			if($status!="disconnected")
			{
				$detect_result_finish = 0;
			}
			else
			{
				$detect_result_finish = 1;
//				$pingresult = query($nowtest_p."/pingresult");
//				if($pingresult=="success")
//				{
					$result_best = query($nowtest_p."/level");
					$result_best_uid = query($nowtest_p."/profileuid");
//				}
			}
		}
		else { TRACE_error("unkonw type:[".$TYPE."]\n"); }
		
	  if($detect_result_finish == 0) { echo 'Trying'; }
	  else
	  {
	  	TRACE_debug("best profile uid:[".$result_best_uid."]\n");
		  if($result_best_uid!="") { echo $result_best_uid; }
			else
			{
				if($TYPE=="3g")
				{
					$lastone = query($3g_status_run_path."/lastone");
					if($lastone==1) { echo 'NoVaildUid'; }
					else { echo 'TryNext'; }
				}
				else if($TYPE=="wisp")
				{
					$lastone = query($wisp_status_run_path."/lastone");
					if($lastone==1) { echo 'NoVaildUid'; }
					else { echo 'TryNext'; }
				}
				else { echo 'NoVaildUid'; }
			}
	  }
	}
}

?>
