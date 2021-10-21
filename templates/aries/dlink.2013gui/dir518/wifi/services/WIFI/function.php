<?
include "/htdocs/phplib/phyinf.php";
include "/htdocs/phplib/xnode.php";
include "/htdocs/phplib/trace.php";
include "/htdocs/webinc/config.php";

function devname($uid)
{
	if($uid == "BAND24G-1.1")
		return $_GLOBALS["BAND24G_DEVNAME"];
	else if($uid == "BAND24G-1.2")
		return $_GLOBALS["BAND24G_GUEST_DEVNAME"];
	else if($uid == "BAND24G-REPEATER")
	{
		$p = XNODE_getpathbytarget("", "phyinf", "uid", "BAND24G-1.1", 0);
		if (query($p."/active")==1) {return $_GLOBALS["BAND24G_REPEATER_DEVNAME"];}
		else {return $_GLOBALS["BAND24G_DEVNAME"];}
	}
	else if($uid == "BAND5G-1.1")
		return $_GLOBALS["BAND5G_DEVNAME"];
	else if($uid == "BAND5G-1.2")
		return $_GLOBALS["BAND5G_GUEST_DEVNAME"];
	else if($uid == "BAND5G-REPEATER")
	{
		$p = XNODE_getpathbytarget("", "phyinf", "uid", "BAND5G-1.1", 0);
		if (query($p."/active")==1) {return $_GLOBALS["BAND5G_REPEATER_DEVNAME"];}
		else {return $_GLOBALS["BAND5G_DEVNAME"];}
	}
	else
	return "";

}
function setssid($wifi_uid,$dev)
{
	$phyp = XNODE_getpathbytarget("", "phyinf", "uid", $wifi_uid, 0);
	$wifi1  = XNODE_getpathbytarget("/wifi", "entry", "uid", query($phyp."/wifi"), 0);
	$ssid = query($wifi1."/ssid");
	$i=0;
	$idx=0;
	$len=strlen($ssid);
	$sub_str=$ssid;
	while($i<$len){
		if( charcodeat($sub_str,$i)=="\\" ||
			charcodeat($sub_str,$i)=="\"" ||
			charcodeat($sub_str,$i)=="$" ||
			charcodeat($sub_str,$i)=="`"){
			$string=$string.substr($sub_str,$idx,$i-$idx);
			$string = $string."\\".charcodeat($sub_str,$i);
			$idx=$i+1;

		}
		$i++;
	}
	if($idx==0){
		$string=$sub_str;
	}
	else if($idx!=$len){
		$string=$string.substr($sub_str,$idx,$len-$idx);
	}	

	fwrite("a", $_GLOBALS["START"], 'iwpriv '.$dev.' set_mib ssid='.'\"'.$string.'\"'.'\n');

}
function getssid($wifi_uid,$dev)
{
	$phyp = XNODE_getpathbytarget("", "phyinf", "uid", $wifi_uid, 0);
	$wifi1  = XNODE_getpathbytarget("/wifi", "entry", "uid", query($phyp."/wifi"), 0);
	$ssid = query($wifi1."/ssid");
	$i=0;
	$idx=0;
	$len=strlen($ssid);
	$sub_str=$ssid;
	while($i<$len){
		if( charcodeat($sub_str,$i)=="\\" ||
			charcodeat($sub_str,$i)=="\"" ||
			charcodeat($sub_str,$i)=="$" ||
			charcodeat($sub_str,$i)=="`"){
			$string=$string.substr($sub_str,$idx,$i-$idx);
			$string = $string."\\".charcodeat($sub_str,$i);
			$idx=$i+1;

		}
		$i++;
	}
	if($idx==0){
		$string=$sub_str;
	}
	else if($idx!=$len){
		$string=$string.substr($sub_str,$idx,$len-$idx);
	}	
	return $string;
}

function setpassphrase($wifi_uid,$dev)
{
	$phyp 	= XNODE_getpathbytarget("", "phyinf", "uid", $wifi_uid, 0);
	$wifi1  = XNODE_getpathbytarget("/wifi", "entry", "uid", query($phyp."/wifi"), 0);
	$psk    = query($wifi1."/nwkey/psk/key");

	$i=0;
	$idx=0;
	$len=strlen($psk);
	$sub_str=$psk;
	while($i<$len){
		if( charcodeat($sub_str,$i)=="\\" ||
			charcodeat($sub_str,$i)=="\"" ||
			charcodeat($sub_str,$i)=="$" ||
			charcodeat($sub_str,$i)=="`"){
			$string=$string.substr($sub_str,$idx,$i-$idx);
			$string = $string."\\".charcodeat($sub_str,$i);
			$idx=$i+1;

		}
		$i++;
	}
	if($idx==0){
		$string=$sub_str;
	}
	else if($idx!=$len){
		$string=$string.substr($sub_str,$idx,$len-$idx);
	}

	fwrite("a", $_GLOBALS["START"], 'iwpriv '.$dev.' set_mib passphrase='.'\"'.$string.'\"'.'\n');	
}
function wisp_profile($winfname,$type,$ssid,$authtype,$encrtype,$psk,$wep_key,$wep_key_len,$ascii)
{
	$i=0;
	$idx=0;
	$len=strlen($ssid);
	$sub_str=$ssid;
	while($i<$len){
		if( charcodeat($sub_str,$i)=="\\" ||
			charcodeat($sub_str,$i)=="\"" ||
			charcodeat($sub_str,$i)=="$" ||
			charcodeat($sub_str,$i)=="`"){
			$string=$string.substr($sub_str,$idx,$i-$idx);
			$string = $string."\\".charcodeat($sub_str,$i);
			$idx=$i+1;

		}
		$i++;
	}
	if($idx==0){
		$string=$sub_str;
	}
	else if($idx!=$len){
		$string=$string.substr($sub_str,$idx,$len-$idx);
	}	
	echo 'iwpriv '.$winfname.' set_mib ssid='.'\"'.$string.'\"'.'\n';
	
	//*******************************************************
	if($authtype== "OPEN") {
		echo 'iwpriv '.$winfname.' set_mib authtype=0\n';//open system
	} else if($authtype== "SHARED"){
		echo 'iwpriv '.$winfname.' set_mib authtype=1\n';//shared key
	} else{
		echo 'iwpriv '.$winfname.' set_mib authtype=2\n';//
	}

	if($encrtype== "NONE") {
		echo 'iwpriv '.$winfname.' set_mib encmode=0\n';//disabled
	} else if($encrtype== "WEP"){
		if($wep_key_len==64){
			echo 'iwpriv '.$winfname.' set_mib encmode=1\n';//WEP64
		}else if($wep_key_len==128){
			echo 'iwpriv '.$winfname.' set_mib encmode=5\n';//WEP128
		}
		echo 'iwpriv '.$winfname.' set_mib wepkey1='.$wep_key_1.'\n';//WEP KEY 1
		echo 'iwpriv '.$winfname.' set_mib wepkey2='.$wep_key_2.'\n';//WEP KEY 2
		echo 'iwpriv '.$winfname.' set_mib wepkey3='.$wep_key_3.'\n';//WEP KEY 3
		echo 'iwpriv '.$winfname.' set_mib wepkey4='.$wep_key_4.'\n';//WEP KEY 4
		echo 'iwpriv '.$winfname.' set_mib wepdkeyid='.$wep_defkey.'\n';//Default Key Index
	} else{
		echo 'iwpriv '.$winfname.' set_mib encmode=2\n';//TKIP or AES
		echo 'iwpriv '.$winfname.' set_mib 802_1x=1\n';
	}

	if($authtype=="WPAPSK"){
		echo 'iwpriv '.$winfname.' set_mib psk_enable=1\n';//WPA-PSK
	} else if($authtype=="WPA2PSK"){
		echo 'iwpriv '.$winfname.' set_mib psk_enable=2\n';//WPA2-PSK
	} else if($authtype=="WPA+2PSK"){
		echo 'iwpriv '.$winfname.' set_mib psk_enable=3\n';//WPA/WPA2-PSK mixed
	} else{
		echo 'iwpriv '.$winfname.' set_mib psk_enable=0\n';//no psk
	}

	if($authtype=="WPAPSK" || $authtype=="WPA+2PSK"){
		if($encrtype=="TKIP"){
			echo 'iwpriv '.$winfname.' set_mib wpa_cipher=2\n';
		} else if($encrtype=="AES"){
			echo 'iwpriv '.$winfname.' set_mib wpa_cipher=8\n';
		} else{
			echo 'iwpriv '.$winfname.' set_mib wpa_cipher=10\n';
		}
	}
	if($authtype=="WPA2PSK" || $authtype=="WPA+2PSK"){
		if($encrtype=="TKIP"){
			echo 'iwpriv '.$winfname.' set_mib wpa2_cipher=2\n';
		} else if($encrtype=="AES"){
			echo 'iwpriv '.$winfname.' set_mib wpa2_cipher=8\n';
		} else{
			echo 'iwpriv '.$winfname.' set_mib wpa2_cipher=10\n';
		}
	}

	if($authtype=="WPAPSK" || $authtype=="WPA2PSK" || $authtype=="WPA+2PSK"){
		echo 'iwpriv '.$winfname.' set_mib passphrase='.$psk.'\n';
	}
}

function multi_ap_profile($dev,$type,$ssid,$authtype,$encrtype,$psk,$wep_key,$wep_key_len,$ascii)
{
	if($type=="script")
	{
		echo 'iwpriv '.$dev.' set_mib ap_profile_enable=1\n';
		echo 'iwpriv '.$dev.' set_mib ap_profile_num=0\n';
	}
	else
	{
		fwrite("a", $_GLOBALS["START"], 'iwpriv '.$dev.' set_mib ap_profile_enable=1\n');
		fwrite("a", $_GLOBALS["START"], 'iwpriv '.$dev.' set_mib ap_profile_num=0\n');
	}

	//$authtype = query("config/authtype");
	//$encrtype = query("config/encrtype");
	//$psk    = query("config/psk/key");
	//$wep_key_len = query("config/wep/size");
	//$ascii = query("config/wep/ascii");
	//$wep_key = query("config/wep/key:1");
	$wep_defkey=0;
	if($ascii==1)
	{
		$wep_key = ascii($wep_key);
	}
	//---------------------------Wireless Security start-------------------------------------------------------//
	if($authtype== "OPEN") 				{$auth=0;} 
	else if($authtype== "SHARED")	{$auth=1;} 
	else													{$auth=2;}

	if($encrtype== "NONE")				{$encr=0;}
	else if($encrtype== "WEP")		
	{
		if($wep_key_len==64)				{$encr=1;}
		else if($wep_key_len==128)	{$encr=2;}
	}

	if($authtype=="WPAPSK")				{$encr=3;}
	else if($authtype=="WPA2PSK")	{$encr=4;}

	if($encrtype=="TKIP")					{$cipher=2;}
	else if($encrtype=="AES")			{$cipher=8;}
	//---------------------------Wireless Security end-------------------------------------------------------//
	if($encr==0)
	{
		$cmd = "iwpriv ".$dev." set_mib ap_profile_add=\"".$ssid."\",".$encr.",".$auth."";
	}
	else if($encr==1 || $encr==2)
	{
		$cmd = "iwpriv ".$dev." set_mib ap_profile_add=\"".$ssid."\",".$encr.",".$auth.",".$wep_defkey.",\"".$wep_key."\",\"".$wep_key."\",\"".$wep_key."\",\"".$wep_key."\"";
	}
	else if($encr==3 || $encr==4)
	{
		$cmd = "iwpriv ".$dev." set_mib ap_profile_add=\"".$ssid."\",".$encr.",".$auth.",".$cipher.",\"".$psk."\"";
	}
	
	if($type=="script")
	{
		echo ''.$cmd.'\n';
	}
	else
	{
		fwrite("a", $_GLOBALS["START"], ''.$cmd.'\n');
	}
}

function setband($wifi_uid,$dev){
	$phyp = XNODE_getpathbytarget("", "phyinf", "uid", $wifi_uid, 0);
	$wifi1  = XNODE_getpathbytarget("/wifi", "entry", "uid", query($phyp."/wifi"), 0);
	//$wlmode = query($phyp."/realtek/wlmode");
	$wlmode = query($phyp."/media/wlmode");
	if($wlmode=="bgn"){
		fwrite("a", $_GLOBALS["START"], 'iwpriv '.$dev.' set_mib band=11\n');
	}
	else if ($wlmode=="b"){
		fwrite("a", $_GLOBALS["START"], 'iwpriv '.$dev.' set_mib band=1\n');
	}
	else if ($wlmode=="g"){
		fwrite("a", $_GLOBALS["START"], 'iwpriv '.$dev.' set_mib band=3\n');
		fwrite("a", $_GLOBALS["START"], 'iwpriv '.$dev.' set_mib deny_legacy=1\n');
	}
	else if ($wlmode=="n"){
		if(strstr($wifi_uid,"5G") != ""){ //a+n - a
			fwrite("a", $_GLOBALS["START"], 'iwpriv '.$dev.' set_mib band=12\n');
			fwrite("a", $_GLOBALS["START"], 'iwpriv '.$dev.' set_mib deny_legacy=4\n');
		}
		else{ //b+g+n -b-g
			fwrite("a", $_GLOBALS["START"], 'iwpriv '.$dev.' set_mib band=11\n');
			fwrite("a", $_GLOBALS["START"], 'iwpriv '.$dev.' set_mib deny_legacy=3\n');
		}
	}
	else if ($wlmode=="bg"){
		fwrite("a", $_GLOBALS["START"], 'iwpriv '.$dev.' set_mib band=3\n');
	}
	else if ($wlmode=="gn"){ //b+g+n -b
		fwrite("a", $_GLOBALS["START"], 'iwpriv '.$dev.' set_mib band=11\n');
		fwrite("a", $_GLOBALS["START"], 'iwpriv '.$dev.' set_mib deny_legacy=1\n');
	}
	else if ($wlmode=="a"){
		fwrite("a", $_GLOBALS["START"], 'iwpriv '.$dev.' set_mib band=4\n');
	}
	else if ($wlmode=="ac"){ //a+n+ac -a-n
		fwrite("a", $_GLOBALS["START"], 'iwpriv '.$dev.' set_mib band=76\n');
		fwrite("a", $_GLOBALS["START"], 'iwpriv '.$dev.' set_mib deny_legacy=12\n');
	}
	else if ($wlmode=="an"){
		fwrite("a", $_GLOBALS["START"], 'iwpriv '.$dev.' set_mib band=12\n');
	}
	else if ($wlmode=="aac"){
		fwrite("a", $_GLOBALS["START"], 'iwpriv '.$dev.' set_mib band=68\n');
	}
	else if ($wlmode=="acn"){ //a+n+ac =a
		fwrite("a", $_GLOBALS["START"], 'iwpriv '.$dev.' set_mib band=76\n');
		fwrite("a", $_GLOBALS["START"], 'iwpriv '.$dev.' set_mib deny_legacy=4\n');
	}
	else if ($wlmode=="acna"){ //a+n+ac
		fwrite("a", $_GLOBALS["START"], 'iwpriv '.$dev.' set_mib band=76\n');
	}
	else{
		fwrite("a", $_GLOBALS["START"], 'iwpriv '.$dev.' set_mib band=11\n');
	}
}

function setfixedrate($wifi_uid,$dev)
{
	$phyp = XNODE_getpathbytarget("", "phyinf", "uid", $wifi_uid, 0);
	$wifi1  = XNODE_getpathbytarget("/wifi", "entry", "uid", query($phyp."/wifi"), 0);
	$mcsauto = query($phyp."/media/dot11n/mcs/auto");
	$fixedrate = query($phyp."/media/txrate");
	$mcsindex = query($phyp."/media/dot11n/mcs/index");

	if($mcsauto==1 && $fixedrate=="auto"){
		fwrite("a", $_GLOBALS["START"], 'iwpriv '.$dev.' set_mib autorate=1\n');
	}
	else{
		fwrite("a", $_GLOBALS["START"], 'iwpriv '.$dev.' set_mib autorate=0\n');
		if($fixedrate!="auto"){
			if($fixedrate=="1")
				fwrite("a", $_GLOBALS["START"], 'iwpriv '.$dev.' set_mib fixrate=1\n');
			else if($fixedrate=="2")
				fwrite("a", $_GLOBALS["START"], 'iwpriv '.$dev.' set_mib fixrate=2\n');
			else if($fixedrate=="5.5")
				fwrite("a", $_GLOBALS["START"], 'iwpriv '.$dev.' set_mib fixrate=4\n');
			else if($fixedrate=="11")
				fwrite("a", $_GLOBALS["START"], 'iwpriv '.$dev.' set_mib fixrate=8\n');
			else if($fixedrate=="6")
				fwrite("a", $_GLOBALS["START"], 'iwpriv '.$dev.' set_mib fixrate=16\n');
			else if($fixedrate=="9")
				fwrite("a", $_GLOBALS["START"], 'iwpriv '.$dev.' set_mib fixrate=32\n');
			else if($fixedrate=="12")
				fwrite("a", $_GLOBALS["START"], 'iwpriv '.$dev.' set_mib fixrate=64\n');
			else if($fixedrate=="18")
				fwrite("a", $_GLOBALS["START"], 'iwpriv '.$dev.' set_mib fixrate=128\n');
			else if($fixedrate=="24")
				fwrite("a", $_GLOBALS["START"], 'iwpriv '.$dev.' set_mib fixrate=256\n');
			else if($fixedrate=="36")
				fwrite("a", $_GLOBALS["START"], 'iwpriv '.$dev.' set_mib fixrate=512\n');
			else if($fixedrate=="48")
				fwrite("a", $_GLOBALS["START"], 'iwpriv '.$dev.' set_mib fixrate=1024\n');
			else//fixrate==54
				fwrite("a", $_GLOBALS["START"], 'iwpriv '.$dev.' set_mib fixrate=2048\n');
		}
		else{//$mcsauto!=1
			if($mcsindex==0)
				fwrite("a", $_GLOBALS["START"], 'iwpriv '.$dev.' set_mib fixrate=4096\n');
			else if($mcsindex==1)
				fwrite("a", $_GLOBALS["START"], 'iwpriv '.$dev.' set_mib fixrate=8192\n');
			else if($mcsindex==2)
				fwrite("a", $_GLOBALS["START"], 'iwpriv '.$dev.' set_mib fixrate=16384\n');
			else if($mcsindex==3)
				fwrite("a", $_GLOBALS["START"], 'iwpriv '.$dev.' set_mib fixrate=32768\n');
			else if($mcsindex==4)
				fwrite("a", $_GLOBALS["START"], 'iwpriv '.$dev.' set_mib fixrate=65536\n');
			else if($mcsindex==5)
				fwrite("a", $_GLOBALS["START"], 'iwpriv '.$dev.' set_mib fixrate=131072\n');
			else if($mcsindex==6)
				fwrite("a", $_GLOBALS["START"], 'iwpriv '.$dev.' set_mib fixrate=262144\n');
			else if($mcsindex==7)
				fwrite("a", $_GLOBALS["START"], 'iwpriv '.$dev.' set_mib fixrate=524288\n');
			else if($mcsindex==8)
				fwrite("a", $_GLOBALS["START"], 'iwpriv '.$dev.' set_mib fixrate=1048576\n');
			else if($mcsindex==9)
				fwrite("a", $_GLOBALS["START"], 'iwpriv '.$dev.' set_mib fixrate=2097152\n');
			else if($mcsindex==10)
				fwrite("a", $_GLOBALS["START"], 'iwpriv '.$dev.' set_mib fixrate=4194304\n');
			else if($mcsindex==11)
				fwrite("a", $_GLOBALS["START"], 'iwpriv '.$dev.' set_mib fixrate=8388608\n');
			else if($mcsindex==12)
				fwrite("a", $_GLOBALS["START"], 'iwpriv '.$dev.' set_mib fixrate=16777216\n');
			else if($mcsindex==13)
				fwrite("a", $_GLOBALS["START"], 'iwpriv '.$dev.' set_mib fixrate=33554432\n');
			else if($mcsindex==14)
				fwrite("a", $_GLOBALS["START"], 'iwpriv '.$dev.' set_mib fixrate=67108864\n');
			else// $mcsindex==15)
				fwrite("a", $_GLOBALS["START"], 'iwpriv '.$dev.' set_mib fixrate=134217728\n');
		}
	}
}

function guestaccess($wifi_uid,$dev)
{
	$phyp = XNODE_getpathbytarget("", "phyinf", "uid", $wifi_uid, 0);
	$wifi1  = XNODE_getpathbytarget("/wifi", "entry", "uid", query($phyp."/wifi"), 0);
	$guestaccess = query("/acl/obfilter/policy");
	if($guestaccess == "" || $guestaccess == "DISABLE")
		fwrite("a", $_GLOBALS["START"], 'iwpriv '.$dev.' set_mib guest_access=0\n'); //0 -->LAN/WAN
	else
	{
		fwrite("a", $_GLOBALS["START"], 'iwpriv '.$dev.' set_mib guest_access=1\n'); //1-->WAN only
		fwrite("a", $_GLOBALS["START"], 'iwpriv '.$dev.' set_mib block_relay=1\n'); //1-->WAN only
	}
}

function setwmm($wifi_uid,$dev)
{
	$phyp = XNODE_getpathbytarget("", "phyinf", "uid", $wifi_uid, 0);
	$wifi1  = XNODE_getpathbytarget("/wifi", "entry", "uid", query($phyp."/wifi"), 0);
	$wmm = query($phyp."/media/wmm/enable");
	if($wmm==1){
		fwrite("a", $_GLOBALS["START"], 'iwpriv '.$dev.' set_mib qos_enable=1\n');
		fwrite("a", $_GLOBALS["START"], 'iwpriv '.$dev.' set_mib apsd_enable=1\n');
	}
	else{
		fwrite("a", $_GLOBALS["START"], 'iwpriv '.$dev.' set_mib qos_enable=0\n');
		fwrite("a", $_GLOBALS["START"], 'iwpriv '.$dev.' set_mib apsd_enable=0\n');
	}
}

function sethiddenssid($wifi_uid,$dev)
{
	$phyp = XNODE_getpathbytarget("", "phyinf", "uid", $wifi_uid, 0);
	$wifi1  = XNODE_getpathbytarget("/wifi", "entry", "uid", query($phyp."/wifi"), 0);
	$ssidhidden = query($wifi1."/ssidhidden");

	if($ssidhidden==1){
		fwrite("a", $_GLOBALS["START"], 'iwpriv '.$dev.' set_mib hiddenAP=1\n');
	}
	else{
		fwrite("a", $_GLOBALS["START"], 'iwpriv '.$dev.' set_mib hiddenAP=0\n');
	}
}

function setup_txpower($uid)
{
	$phypsts= XNODE_getpathbytarget("/runtime", "phyinf", "uid", $uid, 0);
	$dev=devname($uid);
	if($phypsts!=""){
		$ccka	= query($phypsts."/txpower/ccka");
		$cckb	= query($phypsts."/txpower/cckb");
		$sa		= query($phypsts."/txpower/ht401sa");
		$sb		= query($phypsts."/txpower/ht401sb");
		$sa_5G	= query($phypsts."/txpower/ht401sa_5G");
		$sb_5G	= query($phypsts."/txpower/ht401sb_5G");
	}
	else{return;}
	$phyp	= XNODE_getpathbytarget("", "phyinf", "uid", $uid, 0);
	$txpower= query($phyp."/media/txpower");
	if		($txpower=="70"){$tx_value=3;}
	else if ($txpower=="50"){$tx_value=6;}
	else if ($txpower=="25"){$tx_value=12;}
	else					{$tx_value=17;}

	$max_num_24G_ch=14*2;
	$max_num_5G_ch=196*2;

	if($ccka!="" && $cckb!="" && $sa!="" && $sb!=""){
		$index=0;
		while($index<$max_num_24G_ch){
			$ccka_value=substr($ccka,$index,2);
			$ccka_value=strtoul($ccka_value,16);

			if($ccka_value!=0)
			{
				if($ccka_value-$tx_value>=1){$ccka_value=$ccka_value-$tx_value;}
				else						{$ccka_value=1;}
			}
			$ccka_value=dec2strf("%02x",$ccka_value);
			$pwrlevelCCK_A = $pwrlevelCCK_A.$ccka_value;

			$cckb_value=substr($cckb,$index,2);
			$cckb_value=strtoul($cckb_value,16);
			if($cckb_value!=0)
			{
				if($cckb_value-$tx_value>=1){$cckb_value=$cckb_value-$tx_value;}
				else						{$cckb_value=1;}
			}
			$cckb_value=dec2strf("%02x",$cckb_value);
			$pwrlevelCCK_B = $pwrlevelCCK_B.$cckb_value;

			$sa_value=substr($sa,$index,2);
			$sa_value=strtoul($sa_value,16);
			if($sa_value!=0)
			{
				if($sa_value-$tx_value>=1){$sa_value=$sa_value-$tx_value;}
				else						{$sa_value=1;}
			}
			$sa_value=dec2strf("%02x",$sa_value);
			$pwrlevelHT40_1S_A = $pwrlevelHT40_1S_A.$sa_value;

			$sb_value=substr($sb,$index,2);
			$sb_value=strtoul($sb_value,16);
			if($sb_value!=0)
			{
				if($sb_value-$tx_value>=1){$sb_value=$sb_value-$tx_value;}
				else						{$sb_value=1;}
			}
			$sb_value=dec2strf("%02x",$sb_value);
			$pwrlevelHT40_1S_B = $pwrlevelHT40_1S_B.$sb_value;

			$index=$index+2;
		}
		fwrite("a", $_GLOBALS["START"], 'iwpriv '.$dev.' set_mib pwrlevelCCK_A='.$pwrlevelCCK_A.'\n'); 	
		fwrite("a", $_GLOBALS["START"], 'iwpriv '.$dev.' set_mib pwrlevelCCK_B='.$pwrlevelCCK_B.'\n'); 	
		fwrite("a", $_GLOBALS["START"], 'iwpriv '.$dev.' set_mib pwrlevelHT40_1S_A='.$pwrlevelHT40_1S_A.'\n');
		fwrite("a", $_GLOBALS["START"], 'iwpriv '.$dev.' set_mib pwrlevelHT40_1S_B='.$pwrlevelHT40_1S_B.'\n');
	}
	
	if($sa_5G!="" && $sb_5G!=""){
		$index=0;
		while($index<$max_num_5G_ch){
			$sa_value=substr($sa_5G,$index,2);
			$sa_value=strtoul($sa_value,16);
			if($sa_value!=0)
			{
				if($sa_value-$tx_value>=1){$sa_value=$sa_value-$tx_value;}
				else						{$sa_value=1;}
			}
			$sa_value=dec2strf("%02x",$sa_value);
			$pwrlevel5GHT40_1S_A = $pwrlevel5GHT40_1S_A.$sa_value;

			$sb_value=substr($sb_5G,$index,2);
			$sb_value=strtoul($sb_value,16);
			if($sb_value!=0)
			{
				if($sb_value-$tx_value>=1){$sb_value=$sb_value-$tx_value;}
				else						{$sb_value=1;}
			}
			$sb_value=dec2strf("%02x",$sb_value);
			$pwrlevel5GHT40_1S_B = $pwrlevel5GHT40_1S_B.$sb_value;

			$index=$index+2;
		}
		fwrite("a", $_GLOBALS["START"], 'iwpriv '.$dev.' set_mib pwrlevel5GHT40_1S_A='.$pwrlevel5GHT40_1S_A.'\n');
		fwrite("a", $_GLOBALS["START"], 'iwpriv '.$dev.' set_mib pwrlevel5GHT40_1S_B='.$pwrlevel5GHT40_1S_B.'\n');
	}
}

function get_mssid_mac($host_mac,$offset)
{
	$index = 5;
	$mssid_mac = "";
	$carry = 0;

	//loop from low byte to high byte
	//ex: 00:01:02:03:04:05
	//05 -> 04 -> 03 -> 02 -> 01 -> 00
	while($index >= 0)
	{
		$field = cut($host_mac , $index , ":");

		//check mac format
		if($field == "")
			return "";

		//to value
		$value = strtoul($field , 16);
		if($value == "")
			return "";

		if($index == 5)
			$value = $value + $offset;

		//need carry?
		$value = $value + $carry;
		if($value > 255)
		{
			$carry = 1;
			$value = $value % 256;
		}
		else
			$carry = 0;

		//from dec to hex
		$hex_value = dec2strf("%02X" , $value);

		if($mssid_mac == "")
			$mssid_mac = $hex_value;
		else
			$mssid_mac = $hex_value.":".$mssid_mac;

		$index = $index - 1;
	}

	return $mssid_mac;
}
function setup_wps_config($wifi_uid,$dev)
{
	$phyp       = XNODE_getpathbytarget("", "phyinf", "uid", $wifi_uid, 0);
	$wifi1      = XNODE_getpathbytarget("/wifi", "entry", "uid", query($phyp."/wifi"), 0);
	$freq		= query($phyp."/media/freq");
	$ssid 		= query($wifi1."/ssid");
	$wps		= query($wifi1."/wps/enable");
	$wps_pin    = query("wps/pin");         if ($wps_pin == "")     $wps_pin = query("/runtime/devdata/pin");
	$vendor     = query("/runtime/device/vendor");
	$model      = query("/runtime/device/modelname");
	$producturl = query("/runtime/device/producturl");
    $UUID_tmp	= query("/runtime/hostapd/guid");
    $UUID = cut($UUID_tmp,"0","-").cut($UUID_tmp,"1","-").cut($UUID_tmp,"2","-").cut($UUID_tmp,"3","-").cut($UUID_tmp,"4","-");
    $uuid = tolower($UUID);

	$output 	= "/var/run/".$dev.".conf";	
	fwrite("w", $output,"");
	if($wps == "1"){
		fwrite("a", $output,"wlan0_wsc_disabled=0\n");
	}
	else{
		fwrite("a", $output,"wlan0_wsc_disabled=1\n");
	}
	fwrite("a", $output,"upnp = 0\n");
	fwrite("a", $output,"connection_type = 1\n");
	fwrite("a", $output,"ssid = ".$ssid."\n");
	if($freq == "5"){
		fwrite("a", $output,"rf_band = 2\n");
	}
	else{
		fwrite("a", $output,"rf_band = 1\n");
	}
	fwrite("a", $output,"pin_code = ".$wps_pin."\n");
	fwrite("a", $output,"use_ie = 1\n");
	fwrite("a", $output,"auth_type_flags = 39\n");//OPEN|SHARED|WPAPSK|WPA2PSK
	fwrite("a", $output,"encrypt_type_flags = 15\n");//NONE|WEP|TKIP|AES
	fwrite("a", $output,"uuid = ".$uuid."\n");
	fwrite("a", $output,"device_name = ".$model."\n");
	fwrite("a", $output,"manufacturer = ".$vendor." Corporation\n");
	fwrite("a", $output,"manufacturerURL = ".$producturl."\n");
	fwrite("a", $output,"modelURL = ".$producturl."\n");
	fwrite("a", $output,"model_name = ".$model."\n");
	fwrite("a", $output,"model_num = 00000000\n");
	fwrite("a", $output,"serial_num = 00000000\n");
	fwrite("a", $output,"modelDescription = Wireless Router\n");
	fwrite("a", $output,"device_attrib_id = 1\n");
	fwrite("a", $output,"device_oui = 0050f204\n");
	fwrite("a", $output,"device_category_id = 6\n");
	fwrite("a", $output,"device_sub_category_id = 1\n");
	fwrite("a", $output,"device_password_id = 0\n");
	fwrite("a", $output,"tx_timeout = 5\n");
	fwrite("a", $output,"resent_limit = 2\n");
	fwrite("a", $output,"reg_timeout = 120\n");
	fwrite("a", $output,"block_timeout = 60\n");
	// 0x2008|0x480|0x680(CONFIG_METHOD_VIRTUAL_PIN | CONFIG_METHOD_PHYSICAL_PBC | CONFIG_METHOD_VIRTUAL_PBC )
	fwrite("a", $output,"config_method =  9864\n");
}
?>

