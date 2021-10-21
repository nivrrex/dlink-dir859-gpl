<?
include "/htdocs/phplib/phyinf.php";
include "/htdocs/phplib/xnode.php";
include "/htdocs/phplib/trace.php";

function devname($uid)
{
	$p = XNODE_getpathbytarget("", "phyinf", "uid", $uid, 0);
	$wifi = XNODE_getpathbytarget("/wifi",  "entry",  "uid", query($p."/wifi"), 0);
	$opmode = query($wifi."/opmode");
	$freq   = query($p."/media/freq");
	if($opmode=="AP")
	{
		if(strstr($uid,"5G") != "") {$phy_band = "BAND5G"; $wifi_inf = "wlan0";}
		else						{$phy_band = "BAND24G"; $wifi_inf = "wlan1";}
		
		if ($uid==$phy_band."-1.1") 	 {return $wifi_inf;}
		else if ($uid==$phy_band."-1.2") {return $wifi_inf."-va0";}
		else if ($uid==$phy_band."-1.3") {return $wifi_inf."-va1";}
		else if ($uid==$phy_band."-1.4") {return $wifi_inf."-va2";}
		else if ($uid==$phy_band."-1.5") {return $wifi_inf."-va3";}
	}
	else
	{
		if($freq=="24") { $wifi_inf = "wlan1";}
		else			{ $wifi_inf = "wlan0";}

		if($opmode=="STA") {return $wifi_inf;}
		else if($opmode=="REPEATER") {return $wifi_inf."-vxd";}
	}
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
	$enable_routing_zones = query("/acl/firewall2/entry:1/enable");
	$enable_isolation_gzone = query("/acl/firewall2/entry:2/enable");
/*
	$guestaccess = query("/acl/obfilter/policy");
	if($guestaccess == "" || $guestaccess == "DISABLE")
		fwrite("a", $_GLOBALS["START"], 'iwpriv '.$dev.' set_mib guest_access=0\n'); //0 -->LAN/WAN
	else
	{
		fwrite("a", $_GLOBALS["START"], 'iwpriv '.$dev.' set_mib guest_access=1\n'); //1-->WAN only
		fwrite("a", $_GLOBALS["START"], 'iwpriv '.$dev.' set_mib block_relay=1\n'); //1-->WAN only
	}
*/

	if($enable_isolation_gzone == "1")
		fwrite("a", $_GLOBALS["START"], 'iwpriv '.$dev.' set_mib block_relay=1\n');
	else
		fwrite("a", $_GLOBALS["START"], 'iwpriv '.$dev.' set_mib block_relay=0\n');
	
	if($enable_routing_zones == "1")
		fwrite("a", $_GLOBALS["START"], 'iwpriv '.$dev.' set_mib guest_access=0\n');
	else
		fwrite("a", $_GLOBALS["START"], 'iwpriv '.$dev.' set_mib guest_access=1\n');
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

function setup_security($wifi_uid,$dev)
{
	$phyp		= XNODE_getpathbytarget("", "phyinf", "uid", $wifi_uid, 0);
	$wifi1		= XNODE_getpathbytarget("/wifi", "entry", "uid", query($phyp."/wifi"), 0);
	$opmode		= query($wifi1."/opmode");
	$authtype = query($wifi1."/authtype");
	$encrtype = query($wifi1."/encrtype");
	$psk    = query($wifi1."/nwkey/psk/key");
	$wps	= query($wifi1."/wps/enable");
	$wep_key_len = query($wifi1."/nwkey/wep/size");
	$wep_defkey = query($wifi1."/nwkey/wep/defkey") - 1;
	$ascii = query($wifi1."/nwkey/wep/ascii");
	$wep_key_1 = query($wifi1."/nwkey/wep/key:1");
	$wep_key_2 = query($wifi1."/nwkey/wep/key:2");
	$wep_key_3 = query($wifi1."/nwkey/wep/key:3");
	$wep_key_4 = query($wifi1."/nwkey/wep/key:4");
	if($ascii==1)
	{
		$wep_key_1 = ascii($wep_key_1);
		$wep_key_2 = ascii($wep_key_2);
		$wep_key_3 = ascii($wep_key_3);
		$wep_key_4 = ascii($wep_key_4);
	}
	if($authtype== "OPEN") {
		fwrite("a", $_GLOBALS["START"], 'iwpriv '.$dev.' set_mib authtype=0\n');//open system
	} else if($authtype== "SHARED"){
		fwrite("a", $_GLOBALS["START"], 'iwpriv '.$dev.' set_mib authtype=1\n');//shared key
	} else{
		fwrite("a", $_GLOBALS["START"], 'iwpriv '.$dev.' set_mib authtype=2\n');//
	}

	if($encrtype== "NONE") {
		fwrite("a", $_GLOBALS["START"], 'iwpriv '.$dev.' set_mib encmode=0\n');//disabled
	} else if($encrtype== "WEP"){
		if($wep_key_len==64){
			fwrite("a", $_GLOBALS["START"], 'iwpriv '.$dev.' set_mib encmode=1\n');//WEP64
		}else if($wep_key_len==128){
			fwrite("a", $_GLOBALS["START"], 'iwpriv '.$dev.' set_mib encmode=5\n');//WEP128

		}
		fwrite("a", $_GLOBALS["START"], 'iwpriv '.$dev.' set_mib wepkey1='.$wep_key_1.'\n');//WEP KEY 1
		fwrite("a", $_GLOBALS["START"], 'iwpriv '.$dev.' set_mib wepkey2='.$wep_key_2.'\n');//WEP KEY 2
		fwrite("a", $_GLOBALS["START"], 'iwpriv '.$dev.' set_mib wepkey3='.$wep_key_3.'\n');//WEP KEY 3
		fwrite("a", $_GLOBALS["START"], 'iwpriv '.$dev.' set_mib wepkey4='.$wep_key_4.'\n');//WEP KEY 4
		fwrite("a", $_GLOBALS["START"], 'iwpriv '.$dev.' set_mib wepdkeyid='.$wep_defkey.'\n');//Default Key Index
	} else{
		fwrite("a", $_GLOBALS["START"], 'iwpriv '.$dev.' set_mib encmode=2\n');//TKIP or AES
		fwrite("a", $_GLOBALS["START"], 'iwpriv '.$dev.' set_mib 802_1x=1\n');
	}

	if($authtype=="WPAPSK"){
		fwrite("a", $_GLOBALS["START"], 'iwpriv '.$dev.' set_mib psk_enable=1\n');//WPA-PSK
	} else if($authtype=="WPA2PSK"){
		fwrite("a", $_GLOBALS["START"], 'iwpriv '.$dev.' set_mib psk_enable=2\n');//WPA2-PSK
	} else if($authtype=="WPA+2PSK"){
		fwrite("a", $_GLOBALS["START"], 'iwpriv '.$dev.' set_mib psk_enable=3\n');//WPA/WPA2-PSK mixed
	} else{
		fwrite("a", $_GLOBALS["START"], 'iwpriv '.$dev.' set_mib psk_enable=0\n');//no psk
	}

	if($authtype=="WPAPSK" || $authtype=="WPA+2PSK"){
		if($encrtype=="TKIP"){
			fwrite("a", $_GLOBALS["START"], 'iwpriv '.$dev.' set_mib wpa_cipher=2\n');
		} else if($encrtype=="AES"){
			fwrite("a", $_GLOBALS["START"], 'iwpriv '.$dev.' set_mib wpa_cipher=8\n');
		} else{
			fwrite("a", $_GLOBALS["START"], 'iwpriv '.$dev.' set_mib wpa_cipher=10\n');
		}
	}
	if($authtype=="WPA2PSK" || $authtype=="WPA+2PSK"){
		if($encrtype=="TKIP"){
			fwrite("a", $_GLOBALS["START"], 'iwpriv '.$dev.' set_mib wpa2_cipher=2\n');
		} else if($encrtype=="AES"){
			fwrite("a", $_GLOBALS["START"], 'iwpriv '.$dev.' set_mib wpa2_cipher=8\n');
		} else{
			fwrite("a", $_GLOBALS["START"], 'iwpriv '.$dev.' set_mib wpa2_cipher=10\n');
		}
	}

	if($authtype=="WPAPSK" || $authtype=="WPA2PSK" || $authtype=="WPA+2PSK"){
		//fwrite("a", $_GLOBALS["START"], 'iwpriv '.$dev.' set_mib passphrase='.$psk.'\n');
		setpassphrase($wifi_uid,$dev);
	}

	if($opmode == "AP"){
		if($wps=="1"){
			fwrite("a", $_GLOBALS["START"], 'iwpriv '.$dev.' set_mib wsc_enable=2\n');
		}
		else{
			fwrite("a", $_GLOBALS["START"], 'iwpriv '.$dev.' set_mib wsc_enable=0\n');
		}
	}
	else{
		setup_wps_config($wifi_uid,$dev);
		fwrite("a", $_GLOBALS["START"], 'iwpriv '.$dev.' set_mib wsc_enable=0\n');
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
function bandwidth_adjust_for_TW($bandwidth, $channel)
{
	if(get("x", "/runtime/devdata/countrycode") != "TW")
		return $bandwidth; //we don't touch it

	//for band 1: 52, 56, 60, 64
	//HT80 must be: (52, 56, 60, 64)
	//HT40 must be: (52, 56), (60, 64)
	//if we remove 52, we need to downgrade bandwidth for some channels (tom, 20131009)
	if($bandwidth == "80")
	{
		if($channel == "56")
			return "20";

		if($channel == "60" || $channel == "64")
			return "20+40";
	}

	if($bandwidth == "40")
	{
		if($channel == "56")
			return "20";
	}

	return $bandwidth;
}
?>

