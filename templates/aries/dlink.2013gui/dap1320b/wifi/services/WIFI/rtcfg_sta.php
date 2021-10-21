<? /* vi: set sw=4 ts=4: */
/********************************************************************************
 *	NOTE: 
 *		The commands in this configuration generator is based on 
 *		Ralink RT2860 Linux SoftAP Drv1.9.0.0 Release Note and User's Guide.	 
 *		Package Name : rt2860v2_SDK3100_v1900.tar.bz2
 *******************************************************************************/
include "/htdocs/phplib/xnode.php";
include "/htdocs/phplib/trace.php";

/**********************************************************************************/
/* prepare the needed path */
if ($PHY_UID == "") $PHY_UID="WLAN-2";
$PREFIX = cut($PHY_UID, 0,".");
$phy	= XNODE_getpathbytarget("",			"phyinf", "uid", $PHY_UID);
$phyrp	= XNODE_getpathbytarget("/runtime",	"phyinf", "uid", $PHY_UID);
//$wifi	= XNODE_getpathbytarget("/wifi",	"entry",  "uid", query($phy."/wifi"));

//patch for PBC5. If we are doing PBC5, we shouldn't use the origin station profile, to prevent 
//being connected immediately after changing to station. So we use WLAN-4 profile that can't connect.
$file_pbc5 = fread("", "/var/run/DO_WPS_PB5");
if($file_pbc5 != "") 	{ $do_wps_pbc5 = 1 ; }
if($do_wps_pbc5==1)		{ $wifi_prf = "WIFI-4";}
else 					{ $wifi_prf = query($phy."/wifi");}
$wifi	= XNODE_getpathbytarget("/wifi",	"entry",  "uid", $wifi_prf);

/* ----------------------------- get configuration -----------------------------------*/
/* country code */
$ccode = query("/runtime/devdata/countrycode");
if (isdigit($ccode)==1)
{
	TRACE_debug("PHYINF.WIFI service [rtcfg.php (ralink conf)]:".
				"Your country code (".$ccode.") is in number format. ".
				"Please change the country code as ISO name. ".
				"Use 'US' as country code.");
	$ccode = "US";
}
if ($ccode == "")
{
	TRACE_error("PHYINF.WIFI service: no country code! ".
				"Please check the initial value of this board! ".
				"Use 'US' as country code.");
	$ccode = "US";
}

/* we know that GB = EU, but driver doesn't recognize EU. */
if ($ccode == "EU")
{
	TRACE_error("Country code is set to EU. Change it to GB so that driver can recognize it\n");
	$ccode = "GB";
}

if		($ccode == "JP") {$a_region = 9;	$c_region = 1; $RDRegion = "JAP";}
else if	($ccode == "GB") {$a_region = 1;	$c_region = 1; $RDRegion = "CE";}
else if ($ccode == "KR") {$a_region = 5;	$c_region = 1; }
/* use 'US' as default value of $ccode. */
else					 {$a_region = 0;	$c_region = 0;}

/* -------- RT2860AP.dat -------*/
echo "Default"."\n";	/* The word of "Default" must not be removed. */
//if ($c_region != "")	echo "CountryRegion="		.$c_region	."\n";
if ($a_region != "")	echo "CountryRegionABand="	.$a_region	."\n";
echo "CountryCode="			.$ccode		."\n";
echo "WirelessMode=10\n";			//as station, we support A band and G band as default
$macclone =query($phy."/macclone/macaddr");
if($macclone=="")	echo "EthConvertMode=dongle\n";
else				echo "EthConvertMode=hybrid\n";	
echo "EthCloneMac=".$macclone."\n";
echo "NetworkType=Infra\n";

/* authtype */
$auth = query($wifi."/authtype");
if		($auth == "OPEN")			$authtype = "OPEN";
else if ($auth == "SHARED")			$authtype = "SHARED";
//else if ($auth == "WPA")			$authtype = "WPA";
else if ($auth == "WPAPSK")			$authtype = "WPAPSK";
//else if ($auth == "WPA2")			$authtype = "WPA2";
else if ($auth == "WPA2PSK")		$authtype = "WPA2PSK";
//else if ($auth == "WPA+2")			$authtype = "WPA1WPA2";
//else if ($auth == "WPA+2PSK")		$authtype = "WPAPSKWPA2PSK";
else 
{
	TRACE_error("hendry : unknown authtype ".$auth);	
	TRACE_error("hendry : unknown authtype ".$auth);	
	TRACE_error("hendry : unknown authtype ".$auth);	
	TRACE_error("hendry : unknown authtype ".$auth);	
}


/* encrtype */
$encrypt = query($wifi."/encrtype");
if		($encrypt == "NONE")		$encrtype = "NONE";
else if ($encrypt == "WEP")			$encrtype = "WEP";
else if ($encrypt == "TKIP")		$encrtype = "TKIP";
else if ($encrypt == "AES")			$encrtype = "AES";
//else if ($encrypt == "TKIP+AES")	$encrtype = "TKIPAES";
else 
{
	TRACE_error("hendry : unknown encrypt ".$encrtype);	
	TRACE_error("hendry : unknown encrypt ".$encrtype);	
	TRACE_error("hendry : unknown encrypt ".$encrtype);	
	TRACE_error("hendry : unknown encrypt ".$encrtype);	
}

echo "AuthMode=".$authtype."\n";
echo "EncrypType=".$encrtype."\n";

if ($encrypt == "WEP")
{
	$wep++;
	$def = query($wifi."/nwkey/wep/defkey");
	$defkeyid = $def;
	$wepkeytp = query($wifi."/nwkey/wep/ascii");
	echo "Key".$def."Str=".query($wifi."/nwkey/wep/key:".$def)."\n";
	echo "Key".$def."Type=".$wepkeytp."\n";
	echo "DefaultKeyID=".$defkeyid	."\n";
}
if ($authtype=="WPA2PSK" || $authtype=="WPAPSK")
{
	echo "WPAPSK=".query($wifi."/nwkey/psk/key")."\n";
}

if (query($phy."/media/dot11n/bandwidth") == "20")			$bw = 0;
else														$bw = 1;
echo "HT_BW="			.$bw	."\n";

echo "SSID=".query($wifi."/ssid")."\n";

echo "BeaconPeriod=100
TxPower=100
BGProtection=0
TxPreamble=0
RTSThreshold=2347
FragThreshold=2346
TxBurst=1
PktAggregate=0
WmmCapable=1
AckPolicy=0;0;0;0
PSMode=CAM
AutoRoaming=0
RoamThreshold=70
APSDCapable=0
APSDAC=0;0;0;0
HT_RDG=1
HT_OpMode=0
HT_MpduDensity=4
HT_AutoBA=1
HT_BADecline=0
HT_AMSDU=0
HT_BAWinSize=64
HT_GI=1
HT_MCS=33
HT_MIMOPSMode=3
HT_DisallowTKIP=1
IEEE80211H=1
TGnWifiTest=0
WirelessEvent=0
MeshId=MESH
MeshAutoLink=1
MeshAuthMode=OPEN
MeshEncrypType=NONE
MeshWPAKEY=
MeshDefaultkey=1
MeshWEPKEY=
CarrierDetect=0
AntDiversity=0
BeaconLostTime=4
FtSupport=0
Wapiifname=ra0
WapiPsk=
WapiPskType=
WapiUserCertPath=
WapiAsCertPath=
PSP_XLINK_MODE=0
WscManufacturer=
WscModelName=
WscDeviceName=
WscModelNumber=
WscSerialNumber=\n";

/* STBC */
if (query($phy."/media/stbc") == 1 )	{	$stbc_enable = 1;	}
else									{	$stbc_enable = 0;	}
/* videoturbine */
if (query($phy."/media/videoturbine") == 1 )	{	$videoturbine_enable = 1;	}
else											{	$videoturbine_enable = 0;	}
if ( $stbc_enable == 1 )	{	echo "HT_STBC=1"		."\n";	}
else						{	echo "HT_STBC=0"		."\n";	}
if ( $videoturbine_enable == 1 )	{	echo "VideoTurbine=1"		."\n";	}
else								{	echo "VideoTurbine=0"		."\n";	}


/* Ralink's recommendation: Remember modify the TxStream and RxStream according the board supported. */
echo "HT_TxStream=2"					."\n";
echo "HT_RxStream=2"					."\n";

//we set as indoor
echo "ChannelGeography=1\n";

?>
