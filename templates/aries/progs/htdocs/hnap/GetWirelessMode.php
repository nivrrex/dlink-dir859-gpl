HTTP/1.1 200 OK
Content-Type: text/xml; charset=utf-8

<?
echo "\<\?xml version='1.0' encoding='utf-8'\?\>";
include "/htdocs/phplib/trace.php";
include "/htdocs/phplib/xnode.php";
include "/htdocs/webinc/config.php";

//$WLAN_supported_mode = "WirelessRouter,WirelessAp";
$WLAN_supported_mode = "WirelessRouter";

function getWLANBand ($WLANID)
{
	$path_phyinf = XNODE_getpathbytarget("", "phyinf", "uid", $WLANID, 0);
	return query ($path_phyinf."/media/freq");
}
function echoWLANSupportedMode ($WLAN_supported_mode)
{
	echo "\t\t\t\t\<SupportMode\>\n";

	$mode_number = cut_count($WLAN_supported_mode, ",");
	$mode_counter = 0;
	while ($mode_counter < $mode_number)
	{
		echo "\t\t\t\t\t\<string\>".cut($WLAN_supported_mode, $mode_counter, ",")."\</string\>\n";
		$mode_counter ++;
	}

	echo "\t\t\t\t\</SupportMode\>\n";

}

$WLAN1_band = getWLANBand($WLAN1);
if ($WLAN1_band != "")
{
	$RadioID1 = "RADIO_".$WLAN1_band."GHz";
	$WirelessMode1 = "WirelessRouter";
}

$WLAN1_GZ_band = getWLANBand($WLAN1_GZ);
if ($WLAN1_GZ_band != "")
{
	$RadioID1_GZ = "RADIO_".$WLAN1_GZ_band."G_Guest";
	$WirelessMode1_GZ = "WirelessRouter";
}

$WLAN2_band = getWLANBand($WLAN2);
if ($WLAN2_band != "")
{
	$RadioID2 = "RADIO_".$WLAN2_band."GHz";
	$WirelessMode2 = "WirelessRouter";
}

$WLAN2_GZ_band = getWLANBand($WLAN2_GZ);
if ($WLAN2_GZ_band != "")
{
	$RadioID2_GZ = "RADIO_".$WLAN2_GZ_band."G_Guest";
	$WirelessMode2_GZ = "WirelessRouter";
}

?>
<soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
	<soap:Body>
		<GetWirelessModeResponse xmlns="http://purenetworks.com/HNAP1/">
			<GetWirelessModeResult>OK</GetWirelessModeResult>
<?
if ($RadioID1 != "")
{
	echo "\t\t\t\<WirelessModeList\>\n";

	echo "\t\t\t\t\<RadioID\>".$RadioID1."\</RadioID\>\n";
	echo "\t\t\t\t\<WirelessMode1\>".$WirelessMode1."\</WirelessMode1\>\n";

	echoWLANSupportedMode($WLAN_supported_mode);

	echo "\t\t\t\</WirelessModeList\>\n";
}
if ($RadioID1_GZ != "")
{
	echo "\t\t\t\<WirelessModeList\>\n";

	echo "\t\t\t\t\<RadioID\>".$RadioID1_GZ."\</RadioID\>\n";
	echo "\t\t\t\t\<WirelessMode1\>".$WirelessMode1_GZ."\</WirelessMode1\>\n";

	echoWLANSupportedMode($WLAN_supported_mode);

	echo "\t\t\t\</WirelessModeList\>\n";
}
if ($RadioID2 != "")
{
	echo "\t\t\t\<WirelessModeList\>\n";

	echo "\t\t\t\t\<RadioID\>".$RadioID2."\</RadioID\>\n";
	echo "\t\t\t\t\<WirelessMode1\>".$WirelessMode2."\</WirelessMode1\>\n";

	echoWLANSupportedMode($WLAN_supported_mode);

	echo "\t\t\t\</WirelessModeList\>\n";
}
if ($RadioID2_GZ != "")
{
	echo "\t\t\t\<WirelessModeList\>\n";

	echo "\t\t\t\t\<RadioID\>".$RadioID2_GZ."\</RadioID\>\n";
	echo "\t\t\t\t\<WirelessMode1\>".$WirelessMode2_GZ."\</WirelessMode1\>\n";

	echoWLANSupportedMode($WLAN_supported_mode);

	echo "\t\t\t\</WirelessModeList\>\n";
}


?>		</GetWirelessModeResponse>
	</soap:Body>
</soap:Envelope>
